<?php

class Dotdigitalgroup_Email_Model_Resource_Order_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('email_connector/order');
    }




}