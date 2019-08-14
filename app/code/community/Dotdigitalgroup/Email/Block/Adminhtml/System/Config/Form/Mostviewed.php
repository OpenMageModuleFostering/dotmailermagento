<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Form_Mostviewed extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /** label */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $code = Mage::helper('connector')->getPasscode();
        $order = Mage::helper('connector')->getLastOrderNo();
        if(!strlen($code)) $code = '[PLEASE SET UP A PASSCODE]';
        if(!$order) $order = '[PLEASE MAP THE LAST ORDER NO]';
        $text = $baseUrl  . 'connector/email/products/order/@' . $order . '@/code/' . $code . '/mode/mostviewed';
        $element->setData('value', $text);

        return parent::_getElementHtml($element);
    }
}