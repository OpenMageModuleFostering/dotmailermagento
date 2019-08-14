<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_SendCampain
{
    /**
     * send to campain options hours
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>1 , 'label'=>Mage::helper('adminhtml')->__('1 Hour')),
            array('value'=>2 , 'label'=>Mage::helper('adminhtml')->__('2 Hours')),
            array('value'=>3 , 'label'=>Mage::helper('adminhtml')->__('3 Hours')),
            array('value'=>4 , 'label'=>Mage::helper('adminhtml')->__('4 Hours')),
            array('value'=>5 , 'label'=>Mage::helper('adminhtml')->__('5 Hours')),
            array('value'=>6 , 'label'=>Mage::helper('adminhtml')->__('6 Hours')),
            array('value'=>12 , 'label'=>Mage::helper('adminhtml')->__('12 Hours')),
            array('value'=>36 , 'label'=>Mage::helper('adminhtml')->__('36 Hours')),
            array('value'=>48 , 'label'=>Mage::helper('adminhtml')->__('48 Hours')),
            array('value'=>60 , 'label'=>Mage::helper('adminhtml')->__('60 Hours')),
            array('value'=>72 , 'label'=>Mage::helper('adminhtml')->__('72 Hours')),
            array('value'=>84 , 'label'=>Mage::helper('adminhtml')->__('84 Hours')),
            array('value'=>96 , 'label'=>Mage::helper('adminhtml')->__('96 Hours')),
            array('value'=>108 , 'label'=>Mage::helper('adminhtml')->__('108 Hours')),
            array('value'=>120 , 'label'=>Mage::helper('adminhtml')->__('120 Hours')),
        );

    }
}