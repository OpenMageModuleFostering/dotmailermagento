<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Synccustomerdata extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);

    }

    protected function _getAddRowButtonHtml($label) {
        $params = Mage::app()->getRequest()->getParams();

        $url = Mage::helper('adminhtml')->getUrl("*/connector/forcecustomersync", $params);

        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel($label)
            ->setOnClick("window.location.href='" . $url . "'")
            ->toHtml();
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        return $this->_getAddRowButtonHtml($originalData['button_label']);
    }

}