<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_Activebasket
{
    function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => Mage::helper('connector')->__('Success Page')),
            array('value' => '1', 'label' => Mage::helper('connector')->__('Complete Order'))
        );
    }
}