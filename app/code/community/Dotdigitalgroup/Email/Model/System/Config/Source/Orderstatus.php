<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_Orderstatus
{
    public function toOptionArray(){

        $statusCollection = Mage::getModel('sales/order_status')->getCollection();
        $statuses = array();

        foreach ($statusCollection as $one) {
            $statuses[] = array('value' => $one->getStatus(), 'label' => Mage::helper('connector')->__($one->getLabel()));
        }

        return $statuses;
    }
}