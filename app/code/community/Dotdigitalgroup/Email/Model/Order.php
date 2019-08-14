<?php

class Dotdigitalgroup_Email_Model_Order extends Mage_Core_Model_Abstract
{
    const EMAIL_ORDER_NOT_IMPORTED = null;
    /**
     * constructor
     */
    public function _construct(){
        parent::_construct();
        $this->_init('email_connector/order');
    }

    /**
     * @return $this|Mage_Core_Model_Abstract
     */
    protected function _beforeSave(){
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()){
            $this->setCreatedAt($now);
        }
        return $this;
    }


    /**
     * Load the email order by quote id.
     * @param $orderId
     * @param $quoteId
     * @return $this|Varien_Object
     */
    public function loadByOrderId($orderId, $quoteId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('quote_id', $quoteId);
        if($collection->count()){
            return $collection->getFirstItem();
        }else{
            $this->setOrderId($orderId)
                ->setQuoteId($quoteId);
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
    public function getOrdersToImport($storeIds, $limit)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('email_imported', array('null' => true))
            ->addFieldToFilter('store_id', array('in' => $storeIds))
        ;

        $collection->getSelect()->limit($limit);
        return $collection->load();
    }
}