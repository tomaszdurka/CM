<?php

class CM_Maintenance_TasksDefinition extends CM_Service_AbstractNoInstanceDefinition {

    use CM_Service_ManagerAwareTrait;

    public function register(CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
        $this->registerSharedCallbacks();
        $this->registerLocalCallbacks();
    }

    public function registerSharedCallbacks() {
        $this->getServiceManager()->getDefinition('app-maintenance')->onCreate(function (CM_Maintenance_Clockwork $clockwork) {
            $clockwork->registerCallbacks('1 second', [
                'CM_Jobdistribution_DelayedQueue::queueOutstanding' => function () {
                    $delayedQueue = $this->getServiceManager()->getDelayedJobQueue();
                    $delayedQueue->queueOutstanding();
                },
            ]);
            $clockwork->registerCallbacks('1 minute', [
                'CM_Model_User::offlineOld'                 => function () {
                    CM_Model_User::offlineOld();
                },
                'CM_ModelAsset_User_Roles::deleteOld'       => function () {
                    CM_ModelAsset_User_Roles::deleteOld();
                },
                'CM_Paging_Useragent_Abstract::deleteOlder' => function () {
                    CM_Paging_Useragent_Abstract::deleteOlder(100 * 86400);
                },
                'CM_File_UserContent_Temp::deleteOlder'     => function () {
                    CM_File_UserContent_Temp::deleteOlder(86400);
                },
                'CM_SVM_Model::deleteOldTrainings'          => function () {
                    CM_SVM_Model::deleteOldTrainings(3000);
                },
                'CM_Paging_Ip_Blocked::deleteOlder'         => function () {
                    CM_Paging_Ip_Blocked::deleteOld();
                },
                'CM_Captcha::deleteOlder'                   => function () {
                    CM_Captcha::deleteOlder(3600);
                },
                'CM_Session::deleteExpired'                 => function () {
                    CM_Session::deleteExpired();
                },
                'CM_MessageStream_Service::synchronize'     => function () {
                    CM_Service_Manager::getInstance()->getStreamMessage()->synchronize();
                },
            ]);

            if ($this->getServiceManager()->has('janus')) {
                $clockwork->registerCallbacks('1 minute', [
                    'CM_Janus_Service::synchronize'  => function () {
                        $this->getServiceManager()->getJanus('janus')->synchronize();
                    },
                    'CM_Janus_Service::checkStreams' => function () {
                        $this->getServiceManager()->getJanus('janus')->checkStreams();
                    },
                ]);
            }

            $clockwork->registerCallbacks('15 minutes', [
                'CM_Action_Abstract::aggregate'                 => function () {
                    CM_Action_Abstract::aggregate();
                },
                'CM_Action_Abstract::deleteTransgressionsOlder' => function () {
                    CM_Action_Abstract::deleteTransgressionsOlder(3 * 31 * 86400);
                },
                'CM_Paging_Log::cleanup'                        => function () {
                    $allLevelsList = array_values(CM_Log_Logger::getLevels());
                    foreach (CM_Paging_Log::getClassChildren() as $pagingLogClass) {
                        /** @type CM_Paging_Log $log */
                        $log = new $pagingLogClass($allLevelsList);
                        $log->cleanUp();
                    }
                    (new CM_Paging_Log($allLevelsList, false))->cleanUp(); //deletes all untyped records
                },
            ]);
            if ($this->getServiceManager()->has('maxmind')) {
                $clockwork->registerCallbacks('8 days', [
                    'CMService_MaxMind::upgrade' => function () {
                        try {
                            /** @var CMService_MaxMind $maxMind */
                            $maxMind = $this->getServiceManager()->get('maxmind', 'CMService_MaxMind');
                            $maxMind->upgrade();
                        } catch (Exception $exception) {
                            if (!is_a($exception, 'CM_Exception')) {
                                $exception = new CM_Exception($exception->getMessage(), null, [
                                    'file'  => $exception->getFile(),
                                    'line'  => $exception->getLine(),
                                    'trace' => $exception->getTraceAsString(),
                                ]);
                            }
                            $exception->setSeverity(CM_Exception::FATAL);
                            throw $exception;
                        }
                    },
                ]);
            }
        });
    }

    protected function registerLocalCallbacks() {
        $this->getServiceManager()->getDefinition('app-maintenance-local')->onCreate(function (CM_Maintenance_Clockwork $clockwork) {
            $clockwork->registerCallbacks('1 minute', [
                'CM_Cli_CommandManager::monitorSynchronizedCommands' => function () {
                    $commandManager = new CM_Cli_CommandManager();
                    $commandManager->monitorSynchronizedCommands();
                },
                'CM_SVM_Model::trainChanged'                         => function () {
                    CM_SVM_Model::trainChanged();
                },
            ]);
        });
    }
}
