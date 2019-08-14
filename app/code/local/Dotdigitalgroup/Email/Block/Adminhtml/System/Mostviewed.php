<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Mostviewed extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUr = preg_replace('/index.php\//', '', Mage::getBaseUrl());
        $code = Mage::helper('connector')->getPasscode();
        $order = Mage::helper('connector')->getLastOrderNo();
        if(!strlen($code)) $code = '[PLEASE SET UP A PASSCODE]';
        if(!$order) $order = '[PLEASE MAP THE LAST ORDER NO]';
        $text = $baseUr  . 'connector/email/products/order/@' . $order . '@/code/' . $code . '/mode/mostviewed';
        $element->setData('value', $text);
        $element->setData('disabled', 'disabled');
        return parent::_getElementHtml($element);

    }
}