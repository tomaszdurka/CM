<?php

trait CM_Provision_Script_IsLoadedOptionTrait {

    /**
     * @param bool $loaded
     */
    public function setLoaded($loaded) {
        CM_Option::getInstance()->set($this->_getOptionName(), (bool) $loaded);
    }

    /**
     * @return bool
     */
    public function isLoaded() {
        return (bool) CM_Option::getInstance()->get($this->_getOptionName());
    }

    /**
     * @return string
     */
    private function _getOptionName() {
        return 'SetupScript.' . get_class($this);
    }

}