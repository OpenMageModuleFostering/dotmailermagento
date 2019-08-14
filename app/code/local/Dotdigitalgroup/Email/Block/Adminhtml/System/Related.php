<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Related extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUr = preg_replace('/index.php\//', '', Mage::getBaseUrl());
        $helper = Mage::helper('connector');
        $code = $helper->getPasscode();
        $order = $helper->getLastOrderNo();
        if(!strlen($code)) $code = '[PLEASE SET UP A PASSCODE]';
        if(!$order) $order = '[PLEASE MAP THE LAST ORDER NO]';
        $text = $baseUr  . 'connector/email/products/order/@' . $order . '@/code/' . $code . '/mode/related';
        $element->setData('value', $text);

        return parent::_getElementHtml($element);

    }
}