<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Insertvariable extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function _getElementHtml($element){

        $element->setData('onclick', 'templateControl.openVariableChooser();return false;');


        return parent::_getElementHtml($element);
    }

}