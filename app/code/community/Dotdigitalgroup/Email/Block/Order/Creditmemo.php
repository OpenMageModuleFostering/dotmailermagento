<?php
class Dotdigitalgroup_Email_Block_Order_Creditmemo  extends Mage_Sales_Block_Order_Creditmemo_Items
{
    protected function _prepareLayout()
    {
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');
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
        if(! $order->hasCreditmemos()){
            Mage::helper('connector')->log('TE - no creditmemo for order : '. $orderId);
            exit;
        }

        return $order;
    }
    public function getCreditmemoItems()
    {
        return Mage::registry('current_order')->getItemsCollection();
    }
}
