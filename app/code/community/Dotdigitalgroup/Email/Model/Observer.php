<?php

class Dotdigitalgroup_Email_Model_Observer
{

    /**
     * Admin Sync Settings Section
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function actionConfigResetContacts(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('connector');
        $contactModel = Mage::getModel('connector/email_contact');
        $updated = $contactModel->resetCustomerContacts();
        $helper->log('Reset customer contacts for reimport :  ' . $updated);

        return $this;
    }

    /**
     * Admin API Credentials Section
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function actionConfigSaveAfter(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('connector');
        $groups = Mage::app()->getRequest()->getPost('groups');

        //skip if the inherit option is selected
        if(isset($groups['api_credentials']['fields']['username']['value'])
            && isset($groups['api_credentials']['fields']['password']['value'])){
            /** @var Dotdigitalgroup_Email_Model_Connector_Test $testModel */
            $testModel = Mage::getModel('connector/connector_test');

            /**
             * Validate
             */
            $helper->log('VALIDATE ACCOUNT');
            $apiUsername = $groups['api_credentials']['fields']['username']['value'];
            $apiPassword = $groups['api_credentials']['fields']['password']['value'];
            $response = $testModel->validate($apiUsername, $apiPassword );

            if(isset($response->message)){
                Mage::getSingleton('adminhtml/session')->addError($response->message);
            }else{
                /**
                 * Create default data fields
                 */
                $testModel->createDefaultDataFields();
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('API Credentials Valid.'));
            }

        }
        return $this;
    }
    /**
     * Create new contact or update info, also check for email change
     * event: customer_save_before
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleCustomerSaveBefore(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $email      = $customer->getEmail();
        $websiteId  = $customer->getWebsiteId();
        $customerId = $customer->getEntityId();
        try{

            $contactModel = Mage::getModel('connector/email_contact')->loadByCustomerEmail($email, $websiteId);
            $contactModel->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_NOT_IMPORTED)
                ->setCustomerId($customerId)
                ->save();

        }catch(Exception $e){
            Mage::logException($e);
        }

        return $this;
    }
    /**
     * event: sales_order_save_after
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleSalesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getEntityId();
        $quoteId = $order->getQuoteId();
        $storeId = $order->getStoreId();

        try{
            $emailOrder = Mage::getModel('connector/email_order')->loadByOrderId($orderId, $quoteId, $storeId);
            //register the order
            $emailOrder->setUpdatedAt($order->getUpdatedAt())
                ->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_NOT_IMPORTED)
                ->save();

        }catch(Exception $e){
            Mage::logException($e);
        }
        return $this;
    }

    public function newsletterSubscriberSave(Varien_Event_Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $email   = $subscriber->getEmail();
        $storeId = $subscriber->getStoreId();
        $subscriberStatus = $subscriber->getSubscriberStatus();

        $helper = Mage::helper('connector');
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $contactEmail = Mage::getModel('connector/email_contact')->loadByCustomerEmail($email, $websiteId);

        try{
            /**
             * Subscribe a contact
             */
            if($subscriberStatus == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
                $contactEmail->setSubscriberStatus($subscriberStatus)
                    ->setIsSubscriber(1);

            }else{
                /**
                 * Unsubscribe contact
                 */
                $client = Mage::getModel('connector/connector_api_client')
                    ->setApiUsername($helper->getApiUsername($websiteId))
                    ->setApiPassword($helper->getApiPassword($websiteId));

                if(!$contactEmail->getContactId()){
                    //if contact id is not set get the di
                    $result = $client->postContacts($email);
                    $contactId = $result->id;
                }else{
                    $contactId = $contactEmail->getContactId();
                }
                if($contactId){
                    $client->deleteAddressBookContact($helper->getSubscriberAddressBook($websiteId), $contactId);
                }else{
                    Mage::helper('connector')->log('CONTACT ID EMPTY : ' . $contactId . ' email : ' . $email);
                }
                $contactEmail->setIsSubscriber(null);
            }
            $contactEmail->save();

        }catch(Exception $e){
            Mage::helper('connector')->log($e->getMessage());
        }
        return $this;
    }


    public function handleSalesOrderStatusChange(Varien_Event_Observer $observer)
    {
        $order      = $observer->getEvent()->getOrder();
        $status     = $order->getStatus();

        /**
         * SMS functinality
         */
        $smsStatusOne   = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Sms::XML_PATH_SMS_MESSAGE_ONE_STATUS);
        $smsStatusTwo   = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Sms::XML_PATH_SMS_MESSAGE_TWO_STATUS);
        $smsStatusThree = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Sms::XML_PATH_SMS_MESSAGE_THREE_STATUS);
        $smsStatusFour  = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Sms::XML_PATH_SMS_MESSAGE_FOUR_STATUS);
        $smsModel = Mage::getModel('connector/sales_sms');

        switch($status){
            case $smsStatusOne:
                $smsModel->sendMessage($order, 'ONE');
                break;
            case $smsStatusTwo:
                $smsModel->sendMessage($order, 'TWO');
                break;
            case $smsStatusThree:
                $smsModel->sendMessage($order, 'THREE');
                break;
            case $smsStatusFour:
                $smsModel->sendMessage($order, 'FOUR');
                break;
        }

        return $this;
    }

    public function handleSalesOrderRefund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $storeId = $creditmemo->getStoreId();
        $order = $creditmemo->getOrder();
        $orderId = $order->getEntityId();
        $quoteId = $order->getQuoteId();
        $helper = Mage::helper('connector');
        $emailOrder = Mage::getModel('connector/email_order')->loadByOrderId($orderId, $quoteId, $storeId);

        try{
            $emailOrder->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_NOT_IMPORTED)->save();
        }catch (Exception $e){
            $helper->log($e->getMessage());
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
        if($helper->isEnabled($storeId)){

            $client = Mage::getModel('connector/connector_api_client');
            $client->setApiUsername($helper->getApiUsername($websiteId));
            $client->setApiPassword($helper->getApiPassword($websiteId));

            // delete the order transactional data
            $client->deleteContactTransactionalData($customerEmail, 'Orders');
        }

        return $this;
    }

//    public function handleSalesQuoteSaveAfter(Varien_Event_Observer $observer)
//    {
//        $quote = $observer->getEvent()->getQuote();
//        $quoteId = $quote->getId();
//        try{
//
//            $sendModel = Mage::getModel('connector/email_send')->loadByQuoteId($quoteId, $quote->getStoreId());
//
//
//            $sendModel->setEmail($quote->getCustomerEmail())
//                ->setCreatedAt($quote->getCreatedAt())
//                ->setUpdatedAt($quote->getUpdatedAt())->save();
//
//        }catch(Exception $e){
//            Mage::logException($e);
//        }
//
//
//        return $this;
//    }
}