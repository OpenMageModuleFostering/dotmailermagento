<?php
class Dotdigitalgroup_Email_Block_Order_Creditmemo  extends Mage_Sales_Block_Order_Creditmemo_Items
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('connector/sales/order/creditmemo.phtml');
    }

    protected function _prepareLayout()
    {

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getUrl('*/*/history');
        }
        return Mage::getUrl('*/*/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return string
     */
    public function getBackTitle()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::helper('sales')->__('Back to My Orders');
        }
        return Mage::helper('sales')->__('View Another Order');
    }

    public function getInvoiceUrl($order)
    {
        return Mage::getUrl('*/*/invoice', array('order_id' => $order->getId()));
    }

    public function getShipmentUrl($order)
    {
        return Mage::getUrl('*/*/shipment', array('order_id' => $order->getId()));
    }

    public function getViewUrl($order)
    {
        return Mage::getUrl('*/*/view', array('order_id' => $order->getId()));
    }

    public function getPrintCreditmemoUrl($creditmemo){
        return Mage::getUrl('*/*/printCreditmemo', array('creditmemo_id' => $creditmemo->getId()));
    }

    public function getPrintAllCreditmemosUrl($order){
        return Mage::getUrl('*/*/printCreditmemo', array('order_id' => $order->getId()));
    }
}
