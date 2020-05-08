<?php
/*
* @info     Платёжный модуль Epos для JoomShopping
* @package  epos
* @author   esas.by
* @license  GNU/GPL
*/

require_once(JPATH_SITE . '/components/com_jshopping/payments/pm_epos/init.php');

use esas\cmsgate\epos\ConfigFieldsEpos;
use esas\cmsgate\epos\RegistryEposJoomshopping;
use esas\cmsgate\joomshopping\CmsgatePlugin;
use esas\cmsgate\utils\FileUtils;
use esas\cmsgate\utils\Logger;
use esas\cmsgate\view\admin\AdminViewFields;

defined('_JEXEC') or die;

class plgJShoppingEpos extends CmsgatePlugin
{
    function onBeforeSavePayment(&$post)
    {
        try {
            if (isset($_REQUEST[AdminViewFields::CONFIG_FORM_BUTTON_DOWNLOAD_LOG])) {
                FileUtils::downloadByPath(Logger::getLogFilePath());
            } else {
                $configForm = RegistryEposJoomshopping::getRegistry()->getConfigForm();
                $this->saveOrRedirect($configForm);
            }
        } catch (Throwable $e) {
            Logger::getLogger("admin")->error("Exception: ", $e);
        }
    }

}