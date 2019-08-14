<?php
class Dotdigitalgroup_Email_Block_Adminhtml_System_Recentlyviewed extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUrl = $this->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $helper = Mage::helper('connector');
        $code = $helper->getPasscode();
        $customerId = $helper->getMappedCustomerId();

        if(!strlen($code)) $code = '[PLEASE SET UP A PASSCODE]';
        if(!$customerId) $customerId = '[PLEASE MAP THE CUSTOMER ID]';
        $text = $baseUrl  . 'connector/email/products/customer/@' . $customerId . '@/code/' . $code . '/mode/recentlyviewed';
        $element->setData('value', $text);
        $element->setData('disabled', 'disabled');
        return parent::_getElementHtml($element);

    }
}