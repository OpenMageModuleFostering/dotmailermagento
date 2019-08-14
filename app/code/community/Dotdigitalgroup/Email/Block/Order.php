<?php

class Dotdigitalgroup_Email_Block_Order extends Mage_Core_Block_Template
{

    protected function _prepareLayout()
    {
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');

        }
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
    }

    public function getOrder()
    {
        $orderId = Mage::registry('order_id');
        $order = Mage::registry('current_order');
        if(! $orderId){
            $orderId = Mage::app()->getRequest()->getParam('order_id');
            Mage::register('order_id', $orderId);
        }
        if(! $order){
            $order = Mage::getModel('sales/order')->load($orderId);
            Mage::register('current_order', $order);
        }

        return $order;
    }
}