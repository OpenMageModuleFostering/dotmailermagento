<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Smsmessagefour extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    const DEFAULT_TEXT = 'Default SMS Text';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract$element){


        $element->setData('placeholder', self::DEFAULT_TEXT);

        $element->setData('after_element_html', "

            <a href='#' onclick=\"injectText('sms_section_sms_message_four_message', '{{var order_number}}');return false;\">Insert Order Number</a>
            <a href='#' onclick=\"injectText('sms_section_sms_message_four_message', '{{var customer_name}}');return false;\">Insert Customer Name</a>


        ");
        return parent::_getElementHtml($element);
    }


}