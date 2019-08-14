<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_Displayifnot
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'best-sellers', 'label' => Mage::helper('connector')->__('Best Sellers')),
            array('value' => 'most-viewed', 'label' => Mage::helper('connector')->__('Most Viewed'))
        );
    }
}