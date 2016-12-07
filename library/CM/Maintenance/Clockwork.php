<?php

class CM_Maintenance_Clockwork extends CM_Clockwork_Manager {

    /**
     * @param string $dateTimeString
     * @param array  $callbacks
     */
    public function registerCallbacks($dateTimeString, array $callbacks) {
        foreach ($callbacks as $name => $callback) {
            $this->registerCallback($name, $dateTimeString, $callback);
        }
    }

    public function registerCallback($name, $dateTimeString, $callback) {
        $transactionName = 'cm maintenance: ' . $name;
        parent::registerCallback($name, $dateTimeString, function () use ($transactionName, $callback) {
            CM_Service_Manager::getInstance()->getNewrelic()->startTransaction($transactionName);
            try {
                call_user_func_array($callback, func_get_args());
            } catch (CM_Exception $e) {
                CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
                throw $e;
            }
            CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
        });
    }

}
