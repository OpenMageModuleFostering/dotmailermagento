<?php

class  Dotdigitalgroup_Email_Block_Adminhtml_Dashboard_Content extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::_construct();
        $this->_headerText = Mage::helper('connector')->__('Account Managment.');

    }
}