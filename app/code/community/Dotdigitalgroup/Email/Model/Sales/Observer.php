<?php

class Dotdigitalgroup_Email_Model_Sales_Observer
{

    /**
     * Register the order status.
     * @param $observer
     * @return $this
     */
    public function handleSalesOrderSaveBefore($observer)
    {
        $order = $observer->getEvent()->getOrder();
        // the reloaded status
        $reloaded = Mage::getModel('sales/order')->load($order->getId());
        Mage::register('sales_order_status_before', $reloaded->getStatus());
        return $this;
    }
    /**
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleSalesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        try{
            $order = $observer->getEvent()->getOrder();
            $status  = $order->getStatus();
            $storeId = $order->getStoreId();
            $emailOrder = Mage::getModel('email_connector/order')->loadByOrderId($order->getEntityId(), $order->getQuoteId());
            //reimport email order
            $emailOrder->setUpdatedAt($order->getUpdatedAt())
                ->setEmailImported(null)
                ->setStoreId($storeId)
                ->save();
            // check for order status change
            $statusBefore = Mage::registry('sales_order_status_before');
            Mage::helper('connector')->log('Order status : '. $status . ', before : '. $statusBefore);
            if( $status!= $statusBefore){
                $smsCampaign = Mage::getModel('email_connector/sms_campaign', $order);
                $smsCampaign->setStatus($status);
                $smsCampaign->sendSms();
            }
            //admin oder when editing the first one is canceled
            Mage::unregister('sales_order_status_before');
        }catch(Exception $e){
            Mage::logException($e);
        }
        return $this;
    }



    public function handleSalesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        $data = new Varien_Object();
        $order = $observer->getEvent()->getOrder();
        $website = Mage::app()->getWebsite($order->getWebsiteId());
        $websiteName = $website->getName();
        if(Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Transactional::XML_PATH_TRANSACTIONAL_API_ENABLED, $website)){
            $storeName = Mage::app()->getStore($order->getStoreId())->getName();
            $data->setCustomerId($order->getCustomerId())
                ->setCustomerEmail($order->getCustomerEmail())
                ->setOrderId($order->getId())
                ->setOrderIncrementId($order->getIncrementId())
                ->setWebsiteName($websiteName)
                ->setStoreName($storeName)
                ->setWebsite($website)
                ->setOrderDate($order->getCreatedAt());

            Mage::helper('connector/transactional')->updateContactData($data);
        }

        return $this;
    }

    public function handleSalesOrderRefund(Varien_Event_Observer $observer)
    {
        Mage::helper('connector')->log('observer sales order refund');
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $storeId = $creditmemo->getStoreId();
        $order   = $creditmemo->getOrder();
        $orderId = $order->getEntityId();
        $quoteId = $order->getQuoteId();

        try{
            /**
             * Reimport transactional data.
             */
            $emailOrder = Mage::getModel('email_connector/order')->loadByOrderId($orderId, $quoteId, $storeId);
            if(!$emailOrder->getId()){
                Mage::helper('connector')->log('ERROR Creditmemmo Order not found :' . $orderId . ', quote id : ' . $quoteId . ', store id ' . $storeId);
                return $this;
            }
            $emailOrder->setEmailImported(Dotdigitalgroup_Email_Model_Contact::EMAIL_CONTACT_NOT_IMPORTED)->save();
        }catch (Exception $e){
            Mage::logException($e);
        }

        return $this;
    }

    public function hangleSalesOrderCancel(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $customerEmail = $order->getCustomerEmail();
        $helper = Mage::helper('connector');
        if($helper->isEnabled($websiteId)){
            $client = Mage::getModel('email_connector/apiconnector_client');
            $client->setApiUsername($helper->getApiUsername($websiteId));
            $client->setApiPassword($helper->getApiPassword($websiteId));
            // delete the order transactional data
            $client->deleteContactTransactionalData($customerEmail, 'Orders');
        }

        return $this;
    }
}