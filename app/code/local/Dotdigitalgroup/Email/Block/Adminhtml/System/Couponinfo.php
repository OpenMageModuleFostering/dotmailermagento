<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Couponinfo extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUr = preg_replace('/index.php\//', '', Mage::getBaseUrl());
        $code = Mage::helper('connector')->getPasscode();
        if(!strlen($code))
            $code = '[PLEASE SET UP A PASSCODE]';
        $text = $baseUr  . 'connector/email/coupon/id/[INSERT ID HERE]/code/'. $code;

        $element->setData('value', $text);
        $element->setData('disabled', 'disabled');
        return parent::_getElementHtml($element);
    }

}