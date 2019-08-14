<?php

class Dotdigitalgroup_Email_ShippingController extends Mage_Core_Controller_Front_Action
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
        $newOrder = $this->getLayout()->createBlock('email_connector/order_shipping', 'connector_shipping_new', array(
            'template' => 'connector/shipping/new.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $items = $this->getLayout()->createBlock('email_connector/order', 'connector_shipping_items', array(
            'template' => 'connector/order/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_shipping_new')->append($items);
        $items = $this->getLayout()->createBlock('email_connector/order_shipping', 'connector_shipping_track', array(
            'template' => 'email/order/shipment/track.phtml'
        ));
        $this->getLayout()->getBlock('connector_shipping_new')->append($items);
        $this->renderLayout();
    }

    public function newguestAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order_shipping', 'connector_shipping_newguest', array(
            'template' => 'connector/shipping/newguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $items = $this->getLayout()->createBlock('email_connector/order', 'connector_shipping_items', array(
            'template' => 'connector/order/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_shipping_newguest')->append($items);
        $items = $this->getLayout()->createBlock('email_connector/order_shipping', 'connector_shipping_track', array(
            'template' => 'email/order/shipment/track.phtml'
        ));
        $this->getLayout()->getBlock('connector_shipping_newguest')->append($items);
        $this->renderLayout();

    }

    public function updateAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order_shipping', 'connector_shipping_update', array(
            'template' => 'connector/shipping/update.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $this->renderLayout();
    }

    public function updateguestAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order_shipping', 'connector_shipping_updateguest', array(
            'template' => 'connector/shipping/updateguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $this->renderLayout();
    }
}