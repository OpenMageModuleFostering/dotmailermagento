<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Dynamic_Mostviewed extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /** label */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $website = Mage::app()->getRequest()->getParam('website', false);

        if($website){
            $website = Mage::app()->getWebsite($website);
            $baseUrl  = $website->getConfig('web/secure/base_url');
        }
        $passcode = Mage::helper('connector')->getPasscode();

        if(!strlen($passcode)) $passcode = '[PLEASE SET UP A PASSCODE]';

        $text = sprintf('%sconnector/report/mostviewed/code/%s', $baseUrl, $passcode);
        $element->setData('value', $text);

        return parent::_getElementHtml($element);
    }
}