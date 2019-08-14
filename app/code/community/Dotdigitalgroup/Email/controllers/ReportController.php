<?php

class Dotdigitalgroup_Email_ReportController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));


        parent::preDispatch();
    }

    public function bestsellersAction()
    {
        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/recommended_bestsellers', 'connector_customer', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();

    }

    public function mostviewedAction()
    {
        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/recommended_mostviewed', 'connector_customer', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();

    }

    public function recentlyviewedAction()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        if(!$customerId){
            Mage::helper('connector')->log('Recentlyviewed : no customer id : ' . $customerId);
            exit;
        }

        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/recommended_recentlyviewed', 'connector_customer', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();
    }

    public function customerAction()
    {
        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/customer', 'connector_customer', array(
            'template' => 'connector/products.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();
    }



}