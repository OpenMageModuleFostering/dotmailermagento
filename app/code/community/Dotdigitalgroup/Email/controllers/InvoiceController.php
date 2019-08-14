<?php

class Dotdigitalgroup_Email_InvoiceController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $orderId = $this->getRequest()->getParam('order_id', false);
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                Mage::app()->setCurrentStore($order->getStoreId());
            } else {
                Mage::helper('connector')->log('TE : order not found: ' . $orderId);
                exit;
            }
        } else {
            Mage::helper('connector')->log('TE : order_id missing :' . $orderId);
            exit;
        }
        parent::preDispatch();
    }
    public function newAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order_invoice', 'connector_invoice_new', array(
            'template' => 'connector/invoice/new.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $items = $this->getLayout()->createBlock('email_connector/order_invoice', 'connector_order_items', array(
            'template' => 'connector/order/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_invoice_new')->append($items);
        $this->renderLayout();
    }

    public function newguestAction()
    {
        $this->loadLayout();
        $invoice = $this->getLayout()->createBlock('email_connector/order_invoice', 'connector_invoiceguest_new', array(
            'template' => 'connector/invoice/newguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($invoice);
        $items = $this->getLayout()->createBlock('email_connector/order_invoice', 'connector_order_items', array(
            'template' => 'connector/order/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_invoiceguest_new')->append($items);

        $this->renderLayout();

    }
    public function updateAction()
    {
        $this->loadLayout();
        $invoice = $this->getLayout()->createBlock('email_connector/order_invoice', 'connector_invoice_update', array(
            'template' => 'connector/invoice/update.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($invoice);
        $this->renderLayout();
    }
    public function updateguestAction()
    {
        $this->loadLayout();
        $invoice = $this->getLayout()->createBlock('email_connector/order_invoice', 'connector_invoice_updateguest', array(
            'template' => 'connector/invoice/updateguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($invoice);
        $this->renderLayout();
    }

}