<?php

class CM_Maintenance_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @synchronized
     * @keepalive
     */
    public function start() {
        $this->getServiceManager()->get('app-maintenance', CM_Maintenance_Clockwork::class)->start();
    }

    /**
     * @keepalive
     */
    public function startLocal() {
        $this->getServiceManager()->get('app-maintenance-local', CM_Maintenance_Clockwork::class)->start();
    }

    public static function getPackageName() {
        return 'maintenance';
    }
}
