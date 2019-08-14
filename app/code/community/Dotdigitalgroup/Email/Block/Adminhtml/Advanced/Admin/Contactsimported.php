<?php

class Dotdigitalgroup_Email_Block_Adminhtml_Advanced_Admin_Contactsimported extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return $this->_getAddRowButtonHtml($this->__('Count Imported Contacts Now'));
    }

    protected function _getAddRowButtonHtml($title) {
        $url = $this->getUrl("*/debug/countcontacts");

        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($this->__($title))
            ->setOnClick("window.location.href='" . $url . "'")
            ->toHtml();
    }

}
