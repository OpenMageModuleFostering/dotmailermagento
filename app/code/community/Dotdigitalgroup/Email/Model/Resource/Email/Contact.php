<?php

class Dotdigitalgroup_Email_Model_Resource_Email_Contact extends Mage_Core_Model_Mysql4_Abstract
{
    protected  function _construct()
    {
        $this->_init('connector/email_contact', 'email_contact_id');

    }


}