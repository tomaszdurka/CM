<?php

class CM_Clockwork_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string $clockworkClass
     * @param string $storageClass
     * @param string $storageContext
     * @return CM_Clockwork_Manager
     * @throws CM_Exception
     */
    public function createClockwork($clockworkClass, $storageClass, $storageContext) {
        $clockwork = new $clockworkClass();
        if (!$clockwork instanceof CM_Clockwork_Manager) {
            throw new CM_Exception('Invalid clockwork class', ['class' => $clockworkClass]);
        }
        $storage = $this->createStorage($storageClass, $storageContext);
        $clockwork->setStorage($storage);
        return $clockwork;
    }

    /**
     * @param string $storageClass
     * @param string $storageContext
     * @return CM_Clockwork_Storage_Abstract
     * @throws CM_Exception
     *
     */
    public function createStorage($storageClass, $storageContext) {
        $storage = new $storageClass($storageContext);
        if (!$storage instanceof CM_Clockwork_Storage_Abstract) {
            throw new CM_Exception('Invalid storage class', ['class' => $storageClass]);
        }
        if ($storage instanceof CM_Service_ManagerAwareInterface) {
            $storage->setServiceManager($this->getServiceManager());
        }
        return $storage;
    }

}
