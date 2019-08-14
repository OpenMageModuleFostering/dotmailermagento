<?php

class Dotdigitalgroup_Email_Model_Observer extends Dotdigitalgroup_Email_Model_Api_Rest
{
    /**
     * Create new contact or update info also check for email change
     * event: customer_save_before
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleCustomerSaveBefore(Varien_Event_Observer $observer)
    {
        // skip update customer for first time sync
        if(! Mage::registry('first_time_sync')){

            /* @var $customer Mage_Customer_Model_Customer */
            $customer = $observer->getEvent()->getCustomer();
            $email      = $customer->getEmail();
            $websiteId  = $customer->getWebsiteId();
            $subscribed = $customer->getIsSubscribed();

            $this->_api_user     = Mage::app()->getWebsite($websiteId)->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME);
            $this->_api_password = Mage::app()->getWebsite($websiteId)->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD);
            $subscribersAddressBook = Mage::app()->getWebsite($websiteId)->getConfig(Dotdigitalgroup_Email_Model_Newsletter_Subscriber::XML_PATH_SUBSCRIBERS_ADDRESS_BOOK_ID);

            if($customer->getData('dotmailer_contact_id')){
                $dotmailerId = $customer->getData('dotmailer_contact_id');
                //get contact infrmation by id
                $response = $this->getContactById($dotmailerId);

                //check for matching email
                if(isset($response->email)){
                    if($email != $response->email){
                        $data = array(
                            'Email' => $email,
                            'EmailType' => 'Html'
                        );
                        //update the contact with same id - different email
                        $this->updateContact($dotmailerId, $data);
                    }

                    if(!$subscribed && $response->status == 'Subscribed'){
                        $this->deleteAddressBookContact($subscribersAddressBook, $response->id);
                    }
                }
            }else{
                //get contact info by email
                $response = $this->getContactByEmail($email);
                //create new contact and add to address books
                if(isset($response->message) && $response->message == Dotdigitalgroup_Email_Model_Api_Rest::REST_CONTACT_NOT_FOUND){
                    $contactApi = $this->createNewContact($email);
                    $this->postAddressBookContacts(Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CUSTOMERS_ADDRESS_BOOK_ID), $contactApi);

                    if($subscribed){
                        $this->postAddressBookContacts($subscribersAddressBook, $contactApi);
                    }
                }
            }

            if(isset($response->id))
                $customer->setData('dotmailer_contact_id', $response->id);//set the id from contact info
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
        $storeId    = $order->getStoreId();
        $customerId = $order->getCustomerId();

        $customerModel = Mage::getModel('customer/customer')->load($customerId);


        if(!$customerModel->getData('dotmailer_contact_id'))
            $customerModel->save();

        $this->_api_user     = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME, $storeId);
        $this->_api_password = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD, $storeId);
        $is_enabled          = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Order::XML_PATH_TRANSACTIONAL_DATA_ENABLED, $storeId);

        // store scope enabled
        if($is_enabled){
            $dotmailer = new Dotdigitalgroup_Email_Model_Connector_Order($order);
            $this->sendOrderTransactionalData($dotmailer, 'Order');
        }


        return $this;
    }

    /**
     * event: quote_save_after
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleSalesQuoteSaveAfter(Varien_Event_Observer $observer)
    {
        $quote              = $observer->getQuote();
        $customerIsNotGuest = $quote->getCustomerId();
        $storeId            = $quote->getStoreId();

        $this->_api_user     = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME, $storeId);
        $this->_api_password = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD, $storeId);
        $is_enabled          = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Order::XML_PATH_TRANSACTIONAL_DATA_ENABLED, $storeId);

        // save quotes that are active
        if($customerIsNotGuest && $is_enabled && $quote->getIsActive()){
            $dotmailer = new Dotdigitalgroup_Email_Model_Connector_Quote($quote);
            $this->sendTransactionalData($dotmailer, 'Basket');
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
        $smsModel = new Dotdigitalgroup_Email_Model_Sales_Sms();

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

        $this->_api_user     = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME, $storeId);
        $this->_api_password = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD, $storeId);
        $is_enabled          = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Order::XML_PATH_TRANSACTIONAL_DATA_ENABLED, $storeId);
        $order = $creditmemo->getOrder();

        // store scope enabled
        if($is_enabled){
            $connectorOrder = new Dotdigitalgroup_Email_Model_Connector_Order($order);
            $result = $this->sendOrderTransactionalData($connectorOrder, 'Order', $order->getIncrementId());
            $this->_helper->log($result, null, $this->_log_filename);

        }

        return $this;
    }
    public function hangleSalesOrderCancel(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $key  =  $order->getIncrementId();

        $this->_api_user     = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME, $storeId);
        $this->_api_password = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD, $storeId);
        $is_enabled          = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Order::XML_PATH_TRANSACTIONAL_DATA_ENABLED, $storeId);

        if($is_enabled)
            $this->deleteContactsTransactionalData('Order', $key);



        return $this;
    }
}