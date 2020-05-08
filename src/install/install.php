<?php
/*
* @info     Платёжный модуль Epos для JoomShopping
* @package  epos
* @author   esas.by
* @license  GNU/GPL
*/
define('PATH_JSHOPPING', JPATH_SITE . '/components/com_jshopping/');

use esas\cmsgate\epos\ConfigFieldsEpos;
use esas\cmsgate\joomshopping\InstallUtilsJoomshopping;
use esas\cmsgate\Registry;

defined('_JEXEC') or die();
jimport('joomla.filesystem.folder');

class PlgjshoppingeposInstallerScript
{
    public function update()
    {
    }

    public function install($parent)
    {
    }

    public function postflight($type, $parent)
    {
        try {
            self::preInstall('epos');
            self::req('epos');
            InstallUtilsJoomshopping::dbAddPaymentMethod();
            $this->dbAddCompletionText();
            InstallUtilsJoomshopping::dbActivatePlugin();
        } catch (Exception $e) {
            echo JText::sprintf($e->getMessage());
            return false;
        }
    }

    public function uninstall($parent)
    {
        $ret = true;
        self::req('epos');
        $ret = $ret && InstallUtilsJoomshopping::dbDeletePaymentMethod();
        $ret = $ret && $this->dbDeleteCompletionText();
        $ret = $ret && InstallUtilsJoomshopping::deleteFiles();
        return $ret;
    }

    public static function preInstall($paySystemName) {
        //вручную копируем файлы из временной папки, в папку components, иначе не сработают require_once
        $pmPath = JPATH_SITE . '/plugins/jshopping/' . $paySystemName . '/components';
        $newPath = JPATH_SITE . '/components';
        if (!JFolder::copy($pmPath, $newPath, "", true)) {
            throw new Exception('Can not copy folder from[' . $pmPath . '] to [' . $newPath . ']');
        }

    }

    public static function req($paySystemName)
    {
        require_once(PATH_JSHOPPING . 'lib/factory.php');
        require_once(PATH_JSHOPPING . 'payments/pm_' . $paySystemName . '/init.php');
    }

    private function dbAddCompletionText()
    {
        $staticText = new stdClass();
        $staticText->alias = ConfigFieldsEpos::completionText();
        $staticText->use_for_return_policy = 0;
        $jshoppingLanguages = JSFactory::getTable('language', 'jshop');
        foreach ($jshoppingLanguages::getAllLanguages() as $lang) {
            $i18nField = 'text_' . $lang->language;
            $staticText->$i18nField = Registry::getRegistry()->getTranslator()->getConfigFieldDefault(ConfigFieldsEpos::completionText(), $lang->language);
        }
        return JFactory::getDbo()->insertObject('#__jshopping_config_statictext', $staticText);
    }

    private function dbDeleteCompletionText()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('alias') . ' = ' . $db->quote(ConfigFieldsEpos::completionText())
        );
        $query->delete($db->quoteName('#__jshopping_config_statictext'));
        $query->where($conditions);

        $db->setQuery($query);
        return $db->execute();
    }
}