<?php
/*
* @info     Платёжный модуль Epos для JoomShopping
* @package  epos
* @author   esas.by
* @license  GNU/GPL
*/
defined('_JEXEC') or die();

use esas\cmsgate\epos\controllers\ControllerEposCallback;
use esas\cmsgate\joomshopping\CmsgateController;
use esas\cmsgate\utils\Logger as HgLogger;

require_once(dirname(dirname(__FILE__)) . '/payments/pm_epos/init.php');

class JshoppingControllerEpos extends CmsgateController
{
        /**
     * Callback, который вызывает сам ХуткиГрош для оповещение об оплате счета в ЕРИП
     * Тут выполняется дополнительная проверка статуса счета на шлюза и при необходимости изменение его статус заказа
     * в локальной БД
     */
    function notify()
    {
        try {
            $controller = new ControllerEposCallback();
            $controller->process();
        } catch (Throwable $e) {
            HgLogger::getLogger("callback")->error("Exception:", $e);
        }
    }
}