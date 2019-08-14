<?php
class Dotdigitalgroup_Email_Block_Order_Shipping  extends Mage_Sales_Block_Order_Creditmemo_Items
{
    /**
	 * Prepare layout.
	 * @return Mage_Core_Block_Abstract|void
	 */
    protected function _prepareLayout()
    {
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');
        }
    }

    /**
	 * Get current order.
	 * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order|mixed
	 */
    public function getOrder()
    {
        $orderId = Mage::registry('order_id');
        $order = Mage::registry('current_order');
        if (! $orderId) {
            $orderId = Mage::app()->getRequest()->getParam('order_id');
            Mage::register('order_id', $orderId);
        }
        if (! $order) {
            $order = Mage::getModel('sales/order')->load($orderId);
            Mage::register('current_order', $order);
        }
        if (! $order->hasShipments()) {
            Mage::helper('connector')->log('TE - no shipments for order : '. $orderId);
            exit;
        }

        return $order;

    }

    /**
	 * Order shipments.
	 * @return mixed
	 */
    public function getShipment()
    {
        return $shipment = Mage::registry('current_order')->getShipmentsCollection()->getFirstItem();
    }
}
