<?php

class Dotdigitalgroup_Email_OrderController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $orderId = $this->getRequest()->getParam('order_id', false);
        if($orderId){
            $order = Mage::getModel('sales/order')->load($orderId);
            if($order->getId()){
                Mage::app()->setCurrentStore($order->getStoreId());
            }else{
                Mage::helper('connector')->log('TE : order not found: ' . $orderId);
                exit;
            }
        }else{
            Mage::helper('connector')->log('TE : order_id missing :' . $orderId);
            exit;
        }
        parent::preDispatch();
    }
    public function newAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order', 'connector_order', array(
            'template' => 'connector/order/new.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $items = $this->getLayout()->createBlock('email_connector/order', 'connector_order_items', array(
            'template' => 'connector/order/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_order')->append($items);
        $this->renderLayout();
    }

    public function newguestAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order', 'connector_order_guest', array(
            'template' => 'connector/order/newguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $items = $this->getLayout()->createBlock('email_connector/order', 'connector_order_items', array(
            'template' => 'connector/order/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_order_guest')->append($items);
        $this->renderLayout();

    }
    public function updateAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order', 'connector_order_update', array(
            'template' => 'connector/order/update.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $this->renderLayout();
    }
    public function updateguestAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order', 'connector_order_update_guest', array(
            'template' => 'connector/order/updateguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $this->renderLayout();
    }

}