<?php

class Dotdigitalgroup_Email_ProductsController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        if($this->getRequest()->getActionName() != 'push'){
            $orderId = $this->getRequest()->getParam('order_id', false);
            if($orderId){
                $order = Mage::getModel('sales/order')->load($orderId);
                if($order->getId()){
                    Mage::app()->setCurrentStore($order->getStoreId());
                }else{
                    Mage::helper('connector')->log('Dynamic : order not found: ' . $orderId);
                    exit;
                }
            }else{
                Mage::helper('connector')->log('Dynamic : order_id missing :' . $orderId);
                exit;
            }
        }

        parent::preDispatch();
    }
    public function relatedAction()
    {
        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/recommended_products', 'connector_recommended', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);

        $this->renderLayout();

    }
    public function crosssellAction()
    {
        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/recommended_products', 'connector_recommended', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);

        $this->renderLayout();
    }

    public function upsellAction()
    {
        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/recommended_products', 'connector_recommended', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);

        $this->renderLayout();
    }


    public function pushAction()
    {
        $this->loadLayout();
        $products = $this->getLayout()->createBlock('email_connector/recommended_push', 'connector_product', array(
            'template' => 'connector/product/list.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($products);
        $this->renderLayout();
    }


}