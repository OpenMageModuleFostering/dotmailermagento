<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Form_Bestsellers extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /** label */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $helper = Mage::helper('connector');
        $code = $helper->getPasscode();
        $order = $helper->getLastOrderNo();
        if(!strlen($code)) $code = '[PLEASE SET UP A PASSCODE]';
        if(!$order) $order = '[PLEASE MAP THE LAST ORDER NO]';
        $text = $baseUrl  . 'connector/email/products/order/@' . $order . '@/code/' . $code . '/mode/bestsellers';
        $element->setData('value', $text);
        $element->setData('disabled', 'disabled');
        return parent::_getElementHtml($element);

    }
}