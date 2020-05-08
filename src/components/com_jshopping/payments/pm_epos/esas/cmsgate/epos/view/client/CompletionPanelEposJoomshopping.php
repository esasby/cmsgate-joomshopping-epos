<?php
/**
 * Created by IntelliJ IDEA.
 * User: nikit
 * Date: 24.06.2019
 * Time: 14:11
 */

namespace esas\cmsgate\epos\view\client;

use esas\cmsgate\utils\htmlbuilder\Attributes as attribute;
use esas\cmsgate\utils\htmlbuilder\Elements as element;

class CompletionPanelEposJoomshopping extends CompletionPanelEpos
{
    public function getCssClass4MsgSuccess()
    {
        return "alert alert-info";
    }

    public function getCssClass4MsgUnsuccess()
    {
        return "alert alert-error";
    }

    public function getCssClass4Button()
    {
        return "btn btn-success";
    }

    public function getCssClass4TabsGroup()
    {
        return "jshop checkout_payment_block";
    }

    public function getCssClass4Tab()
    {
        return "panel panel-default";
    }

    public function getCssClass4TabHeader()
    {
        return "panel-heading";
    }

    public function getCssClass4TabHeaderLabel()
    {
        return "panel-title";
    }

    public function getCssClass4TabBody()
    {
        return "panel-collapse";
    }

    public function getCssClass4TabBodyContent()
    {
        return "panel-body";
    }


    public function getCssClass4AlfaclickForm()
    {
        return "form-inline";
    }

    public function getCssClass4FormInput()
    {
        return "form-control";
    }

    public function getModuleCSSFilePath()
    {
        return dirname(__FILE__) . "/hiddenRadio.css";
    }

    public function addTabs()
    {
        return element::div(
            attribute::id("table_payments"),
            parent::addTabs());
    }


    public function elementTab($key, $header, $body)
    {
        return
            element::div(
                attribute::clazz("name"),
                element::input(
                    attribute::id("input-" . $key),
                    attribute::type("radio"),
                    attribute::name("payment_method"),
                    attribute::value('pm_' . $key),
                    attribute::onclick("showPaymentForm('pm_" . $key . "')"),
                    attribute::checked($this->isTabChecked($key))
                ),
                element::label(
                    attribute::forr("input-" . $key),
                    element::b($header)
                )

            ) .
            element::div(
                attribute::clazz("paymform"),
                attribute::id("tr_payment_pm_" . $key),
                attribute::style("display: block;"),
                element::div(
                    attribute::clazz("jshop_payment_method"),
                    element::content($body)
                )
            );
    }


}