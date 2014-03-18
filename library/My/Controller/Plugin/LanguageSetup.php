<?php

class My_Controller_Plugin_LanguageSetup extends Zend_Controller_Plugin_Abstract
{

    protected $_languages;
    protected $_directory;

    public function My_Controller_Plugin_LanguageSetup($directory, $languages)
    {
        $this->_dir = $directory;
        $this->_languages = $languages;
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $lang = $this->getRequest()->getParam('lang');
        if (!in_array($lang, array_keys($this->_languages))) {
            $lang = 'es';
        }

        $localeString = $this->_languages[$lang];
        $locale = new Zend_Locale($localeString);

        $file = $this->_dir . '/' . $localeString . '.ini';

        $translate = new Zend_Translate('ini', $file, $localeString);

        Zend_Registry::set('lang', $lang);
        Zend_Registry::set('localeString', $localeString);
        Zend_Registry::set('locale', $locale);
        Zend_Registry::set('Zend_Translate', $translate);
    }
} 