<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 01.10.2018
 * Time: 12:05
 */

namespace esas\cmsgate\epos;

use esas\cmsgate\CmsConnectorJoomshopping;
use esas\cmsgate\descriptors\ModuleDescriptor;
use esas\cmsgate\descriptors\VendorDescriptor;
use esas\cmsgate\descriptors\VersionDescriptor;
use esas\cmsgate\epos\utils\RequestParamsEpos;
use esas\cmsgate\epos\view\client\CompletionPanelEposJoomshopping;
use esas\cmsgate\Registry;
use esas\cmsgate\view\admin\AdminViewFields;
use esas\cmsgate\view\admin\ConfigFormJoomshopping;
use esas\cmsgate\wrappers\SystemSettingsWrapperJoomshopping;

class RegistryEposJoomshopping extends RegistryEpos
{
    public function __construct()
    {
        $this->cmsConnector = new CmsConnectorJoomshopping();
        $this->paysystemConnector = new PaysystemConnectorEpos();
    }


    /**
     * Переопделение для упрощения типизации
     * @return RegistryEposJoomshopping
     */
    public static function getRegistry()
    {
        return parent::getRegistry();
    }

    /**
     * @return ConfigFormJoomshopping
     * @throws \Exception
     */
    public function createConfigForm()
    {
        $managedFields = $this->getManagedFieldsFactory()->getManagedFieldsExcept(AdminViewFields::CONFIG_FORM_COMMON,
            [
                ConfigFieldsEpos::shopName(),
                ConfigFieldsEpos::paymentMethodName(),
                ConfigFieldsEpos::paymentMethodDetails(),
                ConfigFieldsEpos::paymentMethodNameWebpay(),
                ConfigFieldsEpos::paymentMethodDetailsWebpay()
            ]);
        $configForm = new ConfigFormJoomshopping(
            $managedFields,
            AdminViewFields::CONFIG_FORM_COMMON,
            null,
            null);
        $configForm->addSubmitButton(AdminViewFields::CONFIG_FORM_BUTTON_DOWNLOAD_LOG);
        return $configForm;
    }

    public function getCompletionPanel($orderWrapper)
    {
        return new CompletionPanelEposJoomshopping($orderWrapper);
    }

    function getUrlWebpay($orderWrapper)
    {
        return
            SystemSettingsWrapperJoomshopping::generatePaySystemControllerUrl("complete") .
            "&" . RequestParamsEpos::ORDER_NUMBER . "=" . $orderWrapper->getOrderNumber() .
            "&" . RequestParamsEpos::INVOICE_ID . "=" . $orderWrapper->getExtId();
    }

    public function createModuleDescriptor()
    {
        return new ModuleDescriptor(
            "epos",
            new VersionDescriptor("1.11.0", "2020-07-16"),
            "Прием платежей через ЕРИП (сервис EPOS)",
            "https://bitbucket.esas.by/projects/CG/repos/cmsgate-joomshopping-epos/browse",
            VendorDescriptor::esas(),
            "Выставление пользовательских счетов в ЕРИП"
        );
    }
}