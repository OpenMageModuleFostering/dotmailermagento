<?php
class Dotdigitalgroup_Email_Block_Adminhtml_System_Dynamic_Recentlyviewed extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $website = Mage::app()->getRequest()->getParam('website', false);

        if($website){
            $website = Mage::app()->getWebsite($website);
            $baseUrl  = $website->getConfig('web/secure/base_url');
        }
        $passcode = Mage::helper('connector')->getPasscode();
        $customerId = Mage::helper('connector')->getMappedCustomerId();

        if(!strlen($passcode)) $passcode = '[PLEASE SET UP A PASSCODE]';
        if(!$customerId) $customerId = '[PLEASE MAP THE CUSTOMER ID]';
        $text = sprintf('%sconnector/report/recentlyviewed/code/%s/customer_id/@%s@', $baseUrl, $passcode, $customerId);
        $element->setData('value', $text);

        return parent::_getElementHtml($element);

    }
}