<?php

class Dotdigitalgroup_Email_Model_Resource_Email_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('connector/email_order');
    }




}