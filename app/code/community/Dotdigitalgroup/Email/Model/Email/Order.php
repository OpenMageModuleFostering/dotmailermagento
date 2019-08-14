<?php

class Dotdigitalgroup_Email_Model_Email_Order extends Mage_Core_Model_Abstract
{
    /**
     * constructor
     */
    public function _construct(){
        parent::_construct();
        $this->_init('connector/email_order');
    }


    public function loadByOrderId($orderId, $quoteId, $storeId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('store_id', $storeId);
        if($collection->count()){
            return $collection->getFirstItem();
        }else{
            $this->setOrderId($orderId)
                ->setQuoteId($quoteId)
                ->setStoreId($storeId);
        }
        return $this;
    }


    public function getEmailOrderRow($orderId, $quoteId, $storeId)
    {

        $collection = $this->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('store_id', $storeId);

        if($collection->count()){
            return $collection->getFirstItem();
        }else{
            $now = Mage::getSingleton('core/date')->gmtDate();

            $this->setOrderId($orderId)
                ->setQuoteId($quoteId)
                ->setStoreId($storeId)
                ->setCreatedAt($now)
            ;
        }
        return $this;

    }
    public function getOrdersToImport($limit)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('email_imported', array('null' => true));

        $collection->getSelect()->limit($limit);
        return $collection->load();
    }

}