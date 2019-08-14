<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_Timeperiod
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'week',  'label'  => Mage::helper('connector')->__('Week')),
            array('value' => 'month', 'label'  => Mage::helper('connector')->__('Month')),
            array('value' => 'year',  'label'  => Mage::helper('connector')->__('Year'))
        );
    }
}