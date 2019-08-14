<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Form_Customernew extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /** label */
    public function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $helper = Mage::helper('connector');
        $code = $helper->getPasscode();

        $customerId = $helper->getMappedCustomerId();
        if(!strlen($code)) $code = '[PLEASE SET UP A PASSCODE]';

        $text = $baseUrl  . 'connector/customer/new/id/@' . $customerId . '@' . '/code/' . $code;
        $element->setData('value', $text);

        return parent::_getElementHtml($element);
    }

}
