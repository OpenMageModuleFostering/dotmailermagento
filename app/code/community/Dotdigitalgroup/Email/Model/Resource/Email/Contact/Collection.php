<?php

class Dotdigitalgroup_Email_Model_Resource_Email_Contact_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct(){
        parent::_construct();
        $this->_init('connector/email_contact');
    }


    public function addWebsiteFilter($website)
    {
        $this->addFilter('website_id', $website);
        return $this;
    }



}