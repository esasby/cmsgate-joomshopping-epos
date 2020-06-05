<?php
/*
* @info     Платёжный модуль BGPB для JoomShopping
* @package  bgpb
* @author   esas.by
* @license  GNU/GPL
*/


use esas\cmsgate\epos\controllers\ControllerEposAddInvoice;
use esas\cmsgate\epos\controllers\ControllerEposCompletionPage;
use esas\cmsgate\epos\protocol\EposInvoiceAddRs;
use esas\cmsgate\epos\RegistryEposJoomshopping;
use esas\cmsgate\epos\utils\RequestParamsEpos;
use esas\cmsgate\messenger\Messages;
use esas\cmsgate\Registry;
use esas\cmsgate\utils\Logger;
use esas\cmsgate\view\admin\ConfigForm;
use esas\cmsgate\view\admin\ConfigPageJoomshopping;
use esas\cmsgate\view\ViewUtils;
use esas\cmsgate\wrappers\OrderWrapper;
use esas\cmsgate\wrappers\SystemSettingsWrapperJoomshopping;
use JFactory;
use JControllerLegacy;

defined('_JEXEC') or die('Restricted access');
require_once(dirname(__FILE__) . '/init.php');

class pm_epos extends PaymentRoot
{
    /**
     * Отображение формы с настройками платежного шлюза (админка)
     * Для отображения ошибок в форме, сами формы должны быть сохранены в сессии
     * @param $params
     */
    function showAdminFormParams($params)
    {
        try {
            $configForms = new ConfigPageJoomshopping();
            $configFormCommon = RegistryEposJoomshopping::getRegistry()->getConfigForm();
            $this->validateFields($configFormCommon);
            $configForms->addForm($configFormCommon);
            echo $configForms->generate();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage(ViewUtils::logAndGetMsg("admin", $e), 'error');
        } catch (Throwable $e) {
            JFactory::getApplication()->enqueueMessage(ViewUtils::logAndGetMsg("admin", $e), 'error');
        }
    }

    /**
     * @param ConfigForm $configForm
     */
    private function validateFields($configForm)
    {
        if (!$configForm->isValid()) {
            JFactory::getApplication()->enqueueMessage(RegistryEposJoomshopping::getRegistry()->getTranslator()->translate(Messages::INCORRECT_INPUT), 'error');
        }
    }

    const RESP_CODE_OK = '0';
    const RESP_CODE_CANCELED = '2018';

    function checkTransaction($pmconfigs, $order, $act)
    {
        /**
         * Тут никаких проверок ответа от EPOS не делаем, т.к. сделали их ранее в ControllerEposAddInvoice
         * Просто сохраняем враппер в сессии для возможности обращения к нему в getStatusFromResCode
         */
        $orderWrapper = Registry::getRegistry()->getOrderWrapper($order->order_id);
        $session = JFactory::getSession();
        $session->set('orderWrapper', $orderWrapper);
        return array(0, "", $orderWrapper->getExtId());
    }

    /**
     * Важно вернуть тот статус заказа, который был выставлен в ControllerEposAddInvoice
     * Т.к. далее происходит перезапись статуса тем статусом, которые вернет этот метод
     * @param int $rescode
     * @param array $pmconfigs
     * @return mixed
     */
    function getStatusFromResCode($rescode, $pmconfigs)
    {
        $session = JFactory::getSession();
        $orderWrapper=  $session->get('orderWrapper');
        return $orderWrapper->getStatus();
    }

    /**
     * При каких кодах ответов от платежного шлюза считать оплату неуспешной.
     * @return array
     */
    function getNoBuyResCode()
    {
        // в epos большое кол-во кодов неуспешного выставления счета, поэтому для упрощения сводим их все к одному
        return array(self::RESP_CODE_CANCELED);
    }

    /**
     * Форма отображаемая клиенту на step7.
     * @param $pmconfigs
     * @param $order
     * @throws Throwable
     */
    function showEndForm($pmconfigs, $order)
    {
        try {
            $orderWrapper = Registry::getRegistry()->getOrderWrapper($order->order_id);
            $controller = new ControllerEposAddInvoice();
            /**
             * @var EposInvoiceAddRs
             */
            $addBillRs = $controller->process($orderWrapper);
            /**
             * На этом этапе мы только выполняем запрос к HG для добавления счета. Мы не показываем итоговый экран
             * (с кнопками webpay и alfaclick), а выполняем автоматический редирект на step7
             **/

            $redirectParams = array(
                "js_paymentclass" => SystemSettingsWrapperJoomshopping::getPaymentCode(),
                RequestParamsEpos::ORDER_ID => $order->order_id);
            JFactory::getApplication()->redirect(SystemSettingsWrapperJoomshopping::generateControllerPath("checkout", "step7") . '&' . http_build_query($redirectParams));
        } catch (Throwable $e) {
            $this->redirectError($e->getMessage());
        } catch (Exception $e) { // для совместимости с php 5
            $this->redirectError($e->getMessage());
        }

    }

    function redirectError($message)
    {
        JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_jshopping&controller=cart&task=view', FALSE), stripslashes($message), 'error');
    }

    // возможно, уже не надо
    function getUrlParams($pmconfigs)
    {
        $reqest_params = JFactory::getApplication()->input->request->getArray();
        $params = array();
        $params['order_id'] = $reqest_params[RequestParamsEpos::ORDER_ID];
        $params['hash'] = '';
        $params['checkHash'] = false;
        $params['checkReturnParams'] = false;
        return $params;
    }


    /**
     * В теории, тут должно отправлятся уведомление на шлюз об успешном оформлении заказа.
     * В случае с ХуткиГрош мы тут отображаем итоговый экран с доп. кнопками.
     * @param $pmconfigs
     * @param $order
     * @param $payment
     * @throws Throwable
     */
    function complete($pmconfigs, $order, $payment)
    {
        try {
            $controller = new ControllerEposCompletionPage();
            $completionPanel = $controller->process($order->order_id);
            $completionPanel->render();
        } catch (Throwable $e) {
            Logger::getLogger("payment")->error("Exception:", $e);
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }
    }
}

?>