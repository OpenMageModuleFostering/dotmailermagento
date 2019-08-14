<?php

class Dotdigitalgroup_Email_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        parent::preDispatch();
    }
    public function newAction()
    {
        $this->loadLayout();
        $newCustomer = $this->getLayout()->createBlock('email_connector/customer', 'connector_customer', array(
            'template' => 'connector/customer/new.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newCustomer);
        $this->renderLayout();
    }

    public function confirmationAction()
    {
        $this->loadLayout();
        $newCustomer = $this->getLayout()->createBlock('email_connector/customer', 'connector_customer_confirmation', array(
            'template' => 'connector/customer/confirmation.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newCustomer);
        $this->renderLayout();
    }
    public function confirmedAction()
    {
        $this->loadLayout();
        $newCustomer = $this->getLayout()->createBlock('email_connector/customer', 'connector_customer_confirmed', array(
            'template' => 'connector/customer/confirmed.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newCustomer);
        $this->renderLayout();
    }
}
