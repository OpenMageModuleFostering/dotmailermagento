<?php

class Dotdigitalgroup_Email_Model_Sales_Quote
{
    //xml path configuration
    const XML_PATH_LOSTBASKET_1_ENABLED      = 'lostbaskets/customers/enabled_1';
    const XML_PATH_LOSTBASKET_2_ENABLED      = 'lostbaskets/customers/enabled_2';
    const XML_PATH_LOSTBASKET_3_ENABLED      = 'lostbaskets/customers/enabled_3';

    const XML_PATH_LOSTBASKET_1_INTERVAL     = 'lost_basket_settings/customers/send_after_1';
    const XML_PATH_LOSTBASKET_2_INTERVAL     = 'lost_basket_settings/customers/send_after_2';
    const XML_PATH_LOSTBASKET_3_INTERVAL     = 'lost_basket_settings/customers/send_after_3';

    const XML_PATH_TRIGGER_1_CAMPAIGN        = 'lost_basket_settings/customers/campaign_1';
    const XML_PATH_TRIGGER_2_CAMPAIGN        = 'lost_basket_settings/customers/campaign_2';
    const XML_PATH_TRIGGER_3_CAMPAIGN        = 'lost_basket_settings/customers/campaign_3';

    const XML_PATH_GUEST_LOSTBASKET_1_ENABLED  = 'lostbaskets/guests/enabled_1';
    const XML_PATH_GUEST_LOSTBASKET_2_ENABLED  = 'lostbaskets/guests/enabled_2';
    const XML_PATH_GUEST_LOSTBASKET_3_ENABLED  = 'lostbaskets/guests/enabled_3';

    const XML_PATH_GUEST_LOSTBASKET_1_INTERVAL = 'lost_basket_settings/guests/send_after_1';
    const XML_PATH_GUEST_LOSTBASKET_2_INTERVAL = 'lost_basket_settings/guests/send_after_2';
    const XML_PATH_GUEST_LOSTBASKET_3_INTERVAL = 'lost_basket_settings/guests/send_after_3';

    const XML_PATH_GUEST_LOSTBASKET_1_CAMPAIGN = 'lost_basket_settings/guests/campaign_1';
    const XML_PATH_GUEST_LOSTBASKET_2_CAMPAIGN = 'lost_basket_settings/guests/campaign_2';
    const XML_PATH_GUEST_LOSTBASKET_3_CAMPAIGN = 'lost_basket_settings/guests/campaign_3';

    const XML_PATH_TEST_LOSTBASKET_EMAIL       = 'lost_basket_settings/test/email';


    /**
     * send the lost baskets to campains
     * @return array
     */
    public function proccessCampaigns()
    {
        $helper = Mage::helper('connector');

        $client = Mage::getModel('connector/connector_api_client');

        foreach (Mage::app()->getStores() as $store){

            //skip any action if all lost basket campaings are disabled
            if(!$store->getConfig(self::XML_PATH_LOSTBASKET_1_ENABLED) && !$store->getConfig(self::XML_PATH_LOSTBASKET_2_ENABLED) &&
                !$store->getConfig(self::XML_PATH_LOSTBASKET_3_ENABLED) && !$store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_1_ENABLED) &&
                !$store->getconfig(self::XML_PATH_GUEST_LOSTBASKET_2_ENABLED) && !$store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_3_ENABLED)
            )continue;
            // set credentials for every store
            $storeId = $store->getId();
            $websiteId = $store->getWebsite()->getId();

            $client->setApiUsername($helper->getApiUsername($websiteId));
            $client->setApiPassword($helper->getApiPassword($websiteId));

            /**
             * Customers campaings
             */

            //first campign
            if($store->getConfig(self::XML_PATH_LOSTBASKET_1_ENABLED)){

                $contacts = array();
                $from = Zend_Date::now()->subMinute($store->getConfig(self::XML_PATH_LOSTBASKET_1_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                // lost baskets
                $quoteCollection = $this->_getStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));

                if(count($quoteCollection)){
                    // get collection contacts id
                    foreach ($quoteCollection as $quote) {

                        $email = $quote->getCustomerEmail();
                        $contactId = $helper->getContactId($email, $websiteId);

                        if($contactId)
                            $contacts[] = $contactId;
                    }

                    if(!empty($contacts))
                        $client->postCampaignsSend($store->getConfig(self::XML_PATH_TRIGGER_1_CAMPAIGN), $contacts);
                }
            }

            //second campaign
            if($store->getConfig(self::XML_PATH_LOSTBASKET_2_ENABLED)){
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_LOSTBASKET_2_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                // lost baskets
                $quoteCollection = $this->_getStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));

                if(count($quoteCollection)){
                    // get collection contacts id
                    foreach ($quoteCollection as $quote) {

                        $email = $quote->getCustomerEmail();
                        $contactId = $helper->getContactId($email, $websiteId);

                        if($contactId)
                            $contacts[] = $contactId;
                    }

                    if(!empty($contacts))
                        $client->postCampaignsSend($store->getConfig(self::XML_PATH_TRIGGER_2_CAMPAIGN), $contacts);
                }
            }

            //third campign
            if($store->getConfig(self::XML_PATH_LOSTBASKET_3_ENABLED)){
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_LOSTBASKET_3_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                // lost baskets
                $quoteCollection = $this->_getStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));
                if(count($quoteCollection)){
                    // get collection contacts id
                    foreach ($quoteCollection as $quote) {

                        $email = $quote->getCustomerEmail();
                        $contactId = $helper->getContactId($email, $websiteId);

                        if($contactId)
                            $contacts[] = $contactId;
                    }

                    if(!empty($contacts))
                        $client->postCampaignsSend($store->getConfig(self::XML_PATH_TRIGGER_3_CAMPAIGN), $contacts);
                }

            }
            /**
             * Guests campaings
             */
            //first guest campaign
            if($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_1_ENABLED))
            {
                $contacts = array();
                $from = Zend_Date::now()->subMinute($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_1_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                $quoteCollection = $this->_getStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'), true);

                if(count($quoteCollection)){
                    // get collection contacts id
                    foreach ($quoteCollection as $quote) {
                        $email = $quote->getCustomerEmail();
                        //check if the customer exists
                        $response = $client->getContactByEmail($email);

                        if(isset($response->message) && $response->message == Dotdigitalgroup_Email_Model_Connector_Api_Client::REST_CONTACT_NOT_FOUND){
                            //create new contact before sending campaign
                            $contactAPI = $client->postContacts($email);
                            if(!isset($contactAPI->message))
                                $response = $client->postAddressBookContacts($helper->getGuestAddressBook($websiteId), $contactAPI);
                        }
                        $contacts[] = $response->id;
                    }
                    if(!empty($contacts))
                        $client->postCampaignsSend($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_1_CAMPAIGN), $contacts);
                }
            }
            // second guest campaign
            if($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_2_ENABLED))
            {
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_2_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                // lost baskets
                $quoteCollection = $this->_getStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'), true);
                if(count($quoteCollection)){
                    // get collection contacts id
                    foreach ($quoteCollection as $quote) {
                        $email = $quote->getCustomerEmail();
                        //check if the customer exists
                        $response = $client->getContactByEmail($email);

                        if(isset($response->message) && $response->message == Dotdigitalgroup_Email_Model_Connector_Api_Client::REST_CONTACT_NOT_FOUND){
                            //create new contact before sending campaign
                            $contactAPI = $client->postContacts($email);
                            if(!isset($contactAPI->message))
                                $response = $client->postAddressBookContacts($helper->getGuestAddressBook($websiteId), $contactAPI);
                        }
                        $contacts[] = $response->id;
                    }
                    if(!empty($contacts))
                        $client->postCampaignsSend($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_2_CAMPAIGN), $contacts);
                }
            }
            //third guest campaign
            if($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_3_ENABLED)){
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_3_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                // lost baskets
                $quoteCollection = $this->_getStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'), true);
                if(count($quoteCollection)){
                    // get collection contacts id
                    foreach ($quoteCollection as $quote) {
                        $email = $quote->getCustomerEmail();
                        //check if the customer exists
                        $response = $client->getContactByEmail($email);

                        if(isset($response->message) && $response->message == Dotdigitalgroup_Email_Model_Connector_Api_Client::REST_CONTACT_NOT_FOUND){
                            //create new contact before sending campaign
                            $contactAPI = $client->postContacts($email);
                            if(!isset($contactAPI->message))
                                $response = $client->postAddressBookContacts($helper->getGuestAddressBook($websiteId), $contactAPI);
                        }
                        $contacts[] = $response->id;
                    }
                    if(!empty($contacts))
                        $client->postCampaignsSend($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_3_CAMPAIGN), $contacts);
                }
            }
        }
        return;
    }

    /**
     * @param $storeId
     * @param null $from
     * @param null $to
     * @param bool $guest
     * @return Varien_Data_Collection_Db
     */
    private function _getStoreQuotes($storeId, $from = null, $to = null, $guest = false){

        $salesCollection = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('is_active',1)
            ->addFieldToFilter('items_count', array('gt' => 0))
            ->addFieldToFilter('customer_email', array('neq' => ''))
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('updated_at',array(
                    'from' => $from,
                    'to' => $to,
                    'date' => true)
            );

        if($guest)
            $salesCollection->addFieldToFilter('checkout_method' , Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);

        return $salesCollection->load();
    }

}