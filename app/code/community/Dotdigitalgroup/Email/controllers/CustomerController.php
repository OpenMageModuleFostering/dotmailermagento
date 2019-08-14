<?php

class Dotdigitalgroup_Email_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function newAction()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $this->loadLayout();
        $this->renderLayout();
    }
}
