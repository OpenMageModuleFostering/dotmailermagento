<?php

class Dotdigitalgroup_Email_Model_Adminhtml_Source_Orderdelete
{
    /**
     * Returns the values for field order_delete
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0,     'label' => Mage::helper('connector')->__('Do Not Delete')),
            array('value' => 7,     'label' => Mage::helper('connector')->__('7 Days')),
            array('value' => 30,    'label' => Mage::helper('connector')->__('30 Days')),
            array('value' => 90,    'label' => Mage::helper('connector')->__('90 Days')),
            array('value' => 180,   'label' => Mage::helper('connector')->__('180 Days')),
            array('value' => 360,   'label' => Mage::helper('connector')->__('360 Days')),
            array('value' => 720,   'label' => Mage::helper('connector')->__('720 Days'))
            );
    }
}