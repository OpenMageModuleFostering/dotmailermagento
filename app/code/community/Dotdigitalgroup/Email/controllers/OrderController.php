<?php

class Dotdigitalgroup_Email_OrderController extends Mage_Core_Controller_Front_Action
{
    public function newAction()
    {

        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $this->loadLayout();
        $this->renderLayout();

    }

    public function customerRegAction()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function registrationAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function creditmemoAction()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $orderId = $this->getRequest()->getParam('id');
        $order = Mage::getModel('sales/order')->load($orderId);

        //look for refund order
        if(! $order->hasCreditmemos())
            exit();

        Mage::register('current_order', $order);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function invoiceAction()
    {
        //Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $orderId = $this->getRequest()->getParam('id');
        $orderModel = Mage::getModel('sales/order')->load($orderId);

        if(! $orderModel->hasInvoices())
            exit();
        Mage::register('current_order', $orderModel);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function updateAction()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $this->loadLayout();
        $this->renderLayout();

    }

}