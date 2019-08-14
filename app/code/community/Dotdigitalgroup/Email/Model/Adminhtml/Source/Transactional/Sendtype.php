<?php

class Dotdigitalgroup_Email_Model_Adminhtml_Source_Transactional_Sendtype
{

    /**
     * send type options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array('value' => '0', 'label' => Mage::helper('connector')->__('-- Use system default --')),
            array('value' => '1', 'label' => Mage::helper('connector')->__('-- Send via connector --')),
            array('value' => '2', 'label' => Mage::helper('connector')->__('-- Design + Send via connector --'))
        );

        return $options;
    }
}