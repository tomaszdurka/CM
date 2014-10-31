<?php

class CM_App_SetupScript_Core extends CM_Provision_Script_Abstract {

    public function load(CM_Service_Manager $manager) {
        $client = $manager->getDatabases()->getMaster();
        $query = new CM_Db_Query_Insert($client, 'cm_requestClientCounter', ['counter' => 0]);
        $query->execute();
    }
}