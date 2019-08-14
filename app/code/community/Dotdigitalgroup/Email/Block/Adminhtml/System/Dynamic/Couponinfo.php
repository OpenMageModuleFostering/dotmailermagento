<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Dynamic_Couponinfo extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /** label */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
	    //base url
        $baseUrl = Mage::helper('connector')->generateDynamicUrl();


	    //config code
        $code = Mage::helper('connector')->getPasscode();

	    if (!strlen($code))
            $code = '[PLEASE SET UP A PASSCODE]';

	    //full url
	    $text = $baseUrl  . 'connector/email/coupon/id/[INSERT ID HERE]/code/'. $code;

        $element->setData('value', $text);
        $element->setData('disabled', 'disabled');
        return parent::_getElementHtml($element);
    }

}