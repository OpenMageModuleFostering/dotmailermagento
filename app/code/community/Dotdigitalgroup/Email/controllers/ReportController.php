<?php

class Dotdigitalgroup_Email_ReportController extends Mage_Core_Controller_Front_Action
{
	/**
	 * @return Mage_Core_Controller_Front_Action|void
	 */
	public function preDispatch()
    {
	    //authenticate
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        parent::preDispatch();
    }

	/**
	 * Bestsellers report.
	 */
	public function bestsellersAction()
    {
        $this->loadLayout();
	    //set the content template
        $products = $this->getLayout()->createBlock('email_connector/recommended_bestsellers', 'connector_customer', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();
    }

	/**
	 * Most viewed report.
	 */
	public function mostviewedAction()
    {
        $this->loadLayout();
	    //set the content template
        $products = $this->getLayout()->createBlock('email_connector/recommended_mostviewed', 'connector_customer', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();
    }

	/**
	 * Recently viewed products for customer.
	 */
	public function recentlyviewedAction()
    {
	    //customer id param
        $customerId = $this->getRequest()->getParam('customer_id');
	    //no customer was found
        if (! $customerId) {
            Mage::helper('connector')->log('Recentlyviewed : no customer id : ' . $customerId);
            exit();
        }
        $this->loadLayout();
	    //set content tempalate
        $products = $this->getLayout()->createBlock('email_connector/recommended_recentlyviewed', 'connector_customer', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();
    }
}