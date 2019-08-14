<?php

class Dotdigitalgroup_Email_Block_Adminhtml_Contact extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
        $this->_controller         = 'adminhtml_contact';
        $this->_blockGroup         = 'email_connector';
        parent::__construct();
        $this->_headerText         = Mage::helper('connector')->__('Contacts');
        $this->_removeButton('add');

        $this->setTemplate('connector/grid.phtml');
    }
}