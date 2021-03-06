<?php

class CM_Asset_Javascript_Translations extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Model_Language $language
     */
    public function __construct(CM_Model_Language $language) {
        $translations = array();
        foreach ($language->getTranslations(true) as $translation) {
            $translations[$translation['key']] = $language->getTranslation($translation['key']);
        }
        $this->_content = 'cm.language.setAll(' . CM_Params::encode($translations, true) . ');';
    }
}
