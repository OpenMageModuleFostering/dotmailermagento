<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_Displaytype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'grid', 'label' => Mage::helper('connector')->__('Grid')),
            array('value' => 'list', 'label' => Mage::helper('connector')->__('List'))
        );

    }
}