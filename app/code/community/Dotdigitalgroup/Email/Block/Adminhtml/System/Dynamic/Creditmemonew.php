<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Dynamic_Creditmemonew extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $website = Mage::app()->getRequest()->getParam('website', false);

        if ($website) {
            $website = Mage::app()->getWebsite($website);
            $baseUrl  = $website->getConfig('web/secure/base_url');
        }
        $helper = Mage::helper('connector');
        $code = $helper->getPasscode();
        $orderId = $helper->getMappedOrderId();

        if(!strlen($code))
            $code = '[PLEASE SET UP A PASSCODE]';
        $text = sprintf('%s/connector/creditmemo/new/code/%s/id/@%s@', $baseUrl, $code, $orderId);

        $element->setData('value', $text);

        return parent::_getElementHtml($element);
    }

}