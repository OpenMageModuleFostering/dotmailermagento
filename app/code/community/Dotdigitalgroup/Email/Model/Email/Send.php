<?php

class Dotdigitalgroup_Email_Model_Email_Send extends Mage_Core_Model_Abstract
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
     * constructor
     */
    public function _construct(){
        parent::_construct();
        $this->_init('connector/email_send');
    }


    /**
     * @param $quoteId
     * @param $storeId
     * @return mixed
     */
    public function loadByQuoteId($quoteId, $storeId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('store_id', $storeId);

        if($collection->count())
            return $collection->getFirstItem();
        else
            $this->setQuoteId($quoteId)
                ->setStoreId($storeId);

        return $this;
    }




    /**
     * send the lost baskets to campains
     * @return array
     */
    public function sendLostBasketsEmail()
    {
        $helper = Mage::helper('connector');
        $client = Mage::getModel('connector/connector_api_client');
        $salesQuote = Mage::getModel('connector/sales_quote');

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
            if(!$store->getConfig(self::XML_PATH_LOSTBASKET_1_ENABLED)){

                $contacts = array();
                $from = Zend_Date::now()->subMinute($store->getConfig(self::XML_PATH_LOSTBASKET_1_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);

                // sales quotes for guests
                $quoteCollection = $salesQuote->getGuestStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));

                foreach($quoteCollection as $one) {

                    $email = $one->getEmail();
                    if(isset($email)){
                        $campaignId = $store->getConfig(self::XML_PATH_TRIGGER_1_CAMPAIGN);
                        $contactModel = Mage::getModel('connector/email_contact')->loadByCustomerEmail($email, $websiteId);

                        if($contactId = $contactModel->getContactId()){
                            $contacts[] = $contactId;
                        }else{

                            $result = $client->getContactByEmail($email);
                            if(isset($result->id)){
                                $contactModel->setContactId($result->id)->save();
                                $contacts[] = $result->id;
                            }
                        }
                    }
                }
//                if(count($contacts)){
//                    $result = $client->postCampaignsSend($campaignId, $contacts);
//                    if(isset($result->message)){
//
//                        foreach ($emailsToSent as $one){
//                            $one->setCapmaignId($campaignId)
//                                ->setMessage($result->message)->save();
//                        }
//                    }else{
//                        foreach ($emailsToSent as $one) {
//
//                            $one->setCampaignId($campaignId)
//                                ->setIsSent(1)
//                                ->setSentAt(Varien_Date::now())
//                                ->save();
//                        }
//                    }
//                }
            }

            //second campaign
            if(!$store->getConfig(self::XML_PATH_LOSTBASKET_2_ENABLED)){
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_LOSTBASKET_2_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                // lost baskets
                $emailsToSent = $this->_getEmailToSent($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));

                foreach($emailsToSent as $one) {

                    $email = $one->getEmail();
                    if(isset($email)){
                        $campaignId = $store->getConfig(self::XML_PATH_TRIGGER_2_CAMPAIGN);

                        $contactModel = Mage::getModel('connector/email_contact')->loadByCustomerEmail($email, $websiteId);

                        if($contactId = $contactModel->getContactId()){
                            $contacts[] = $contactId;
                        }else{

                            $result = $client->getContactByEmail($email);
                            if(isset($result->id)){
                                $contactModel->setContactId($result->id)->save();
                                $contacts[] = $result->id;
                            }
                        }
                    }
                }
                if(count($contacts)){
                    $result = $client->postCampaignsSend($campaignId, $contacts);
                    if(isset($result->message)){

                        foreach ($emailsToSent as $one){
                            $one->setCapmaignId($campaignId)
                                ->setMessage($result->message)->save();
                        }
                    }else{
                        foreach ($emailsToSent as $one) {

                            $one->setCampaignId($campaignId)
                                ->setIsSent(1)
                                ->setSentAt(Varien_Date::now())
                                ->save();
                        }

                    }
                }
            }

            //third campign
            if(!$store->getConfig(self::XML_PATH_LOSTBASKET_3_ENABLED)){
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_LOSTBASKET_3_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                // lost baskets
                $emailsToSent = $this->_getEmailToSent($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));

                foreach($emailsToSent as $one) {

                    $email = $one->getEmail();
                    if(isset($email)){
                        $campaignId = $store->getConfig(self::XML_PATH_TRIGGER_3_CAMPAIGN);
                        $contactModel = Mage::getModel('connector/email_contact')->loadByCustomerEmail($email, $websiteId);
                        if($contactId = $contactModel->getContactId()){
                            $contacts[] = $contactId;
                        }else{

                            $result = $client->getContactByEmail($email);
                            if(isset($result->id)){
                                $contactModel->setContactId($result->id)->save();
                                $contacts[] = $result->id;
                            }
                        }
                    }
                }
                if(count($contacts)){
                    $result = $client->postCampaignsSend($campaignId, $contacts);
                    if(isset($result->message)){

                        foreach ($emailsToSent as $one){
                            $one->setCapmaignId($campaignId)
                                ->setMessage($result->message)->save();
                        }
                    }else{
                        foreach ($emailsToSent as $one) {
                            $one->setCampaignId($campaignId)
                                ->setIsSent(1)
                                ->setSentAt(Varien_Date::now())
                                ->save();
                        }
                    }
                }
            }
            /**
             * Guests campaings
             */
            //first guest campaign
            if(!$store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_1_ENABLED))
            {
                $contacts = array();
                $from = Zend_Date::now()->subMinute($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_1_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);

                // sales quotes for guests
                $quoteCollection = $salesQuote->getGuestStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));

                foreach($quoteCollection as $quote) {
                    $email = $quote->getCustomerEmail();
                    $campaignId = $store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_1_CAMPAIGN);

                    $result = $client->postContacts($email);
                    if(isset($result->id)){
                        $contacts[] = $result->id;
                    }

                }
                if(count($contacts)){
                    /**
                     * Send Lost Basket Campaign For Guests
                     */
                    $result = $client->postCampaignsSend($campaignId, $contacts);

                    if(isset($result->message)){
                        //save report message for this contact
                        foreach ($quoteCollection as $quote){

                            $quoteId = $quote->getId();
                            $emailModel = $this->loadByQuoteId($quoteId, $storeId);
                            $emailModel->setEmail($email)
                                ->setQuoteId($quote->getId())
                                ->setCreatedAt($quote->getCreatedAt())
                                ->setUpdatedAt($quote->getUpdatedAt())
                            ;

                            $emailModel->setCampaignId($campaignId)
                                ->setMessage($result->message)->save();
                        }
                    }else{
                        //mark contacts as sent
                        foreach ($quoteCollection as $quote) {
                            $email = $quote->getCustomerEmail();
                            $storeId = $quote->getStoreId();

                            $collection = $this->getCollection()
                                ->addFieldToFilter('email', $email);
                            if($collection->count()){
                                $send = $collection->getFirstItem();
                            }else{
                                $send = $this;
                                $send->setEmail($email);
                            }
                            $send->setSentAt(Varien_Date::now())
                                ->setIsSent(1)
                                ->setCampaignId($campaignId)->save();
                        }

                    }
                }
            }
            // second guest campaign
            if(!$store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_2_ENABLED))
            {
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_2_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                $quoteCollection = $salesQuote->getGuestStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));
                foreach($quoteCollection as $quote) {
                    $email = $quote->getCustomerEmail();
                    $campaignId = $store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_2_CAMPAIGN);

                    $result = $client->postContacts($email);
                    if(isset($result->id)){
                        $contacts[] = $result->id;
                    }

                }
                if(count($contacts)){
                    /**
                     * Send Lost Basket Campaign For Guests
                     */
                    $result = $client->postCampaignsSend($campaignId, $contacts);

                    if(isset($result->message)){
                        //save report message for this contact
                        foreach ($quoteCollection as $quote){

                            $quoteId = $quote->getId();
                            $emailModel = $this->loadByQuoteId($quoteId, $storeId);
                            $emailModel->setEmail($email)
                                ->setQuoteId($quote->getId())
                                ->setCreatedAt($quote->getCreatedAt())
                                ->setUpdatedAt($quote->getUpdatedAt())
                            ;
                            $emailModel->setCampaignId($campaignId)
                                ->setMessage($result->message)->save();
                        }
                    }else{
                        //mark contacts as sent
                        foreach ($quoteCollection as $quote) {
                            $email = $quote->getCustomerEmail();
                            $storeId = $quote->getStoreId();
                            $collection = $this->getCollection()
                                ->addFieldToFilter('email', $email);
                            if($collection->count()){
                                $send = $collection->getFirstItem();
                            }else{
                                $send = $this;
                                $send->setEmail($email);
                            }
                            $send->setSentAt(Varien_Date::now())
                                ->setIsSent(1)
                                ->setCampaignId($campaignId)->save();
                        }
                    }
                }
            }
            //third guest campaign
            if(!$store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_3_ENABLED)){
                $contacts = array();
                $from = Zend_Date::now()->subHour($store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_3_INTERVAL));
                $to = clone($from);
                $from->sub('5', Zend_Date::MINUTE);
                $quoteCollection = $salesQuote->getGuestStoreQuotes($storeId, $from->toString('YYYY-MM-dd HH:mm:ss'), $to->toString('YYYY-MM-dd HH:mm:ss'));
                foreach($quoteCollection as $quote) {
                    $email = $quote->getCustomerEmail();
                    $campaignId = $store->getConfig(self::XML_PATH_GUEST_LOSTBASKET_3_CAMPAIGN);

                    $result = $client->postContacts($email);
                    if(isset($result->id)){
                        $contacts[] = $result->id;
                    }

                }
                if(count($contacts)){
                    /**
                     * Send Lost Basket Campaign For Guests
                     */
                    $result = $client->postCampaignsSend($campaignId, $contacts);

                    if(isset($result->message)){
                        //save report message for this contact
                        foreach ($quoteCollection as $quote){

                            $quoteId = $quote->getId();
                            $emailModel = $this->loadByQuoteId($quoteId, $storeId);
                            $emailModel->setEmail($email)
                                ->setQuoteId($quote->getId())
                                ->setCreatedAt($quote->getCreatedAt())
                                ->setUpdatedAt($quote->getUpdatedAt())
                            ;
                            $emailModel->setCampaignId($campaignId)
                                ->setMessage($result->message)->save();
                        }
                    }else{
                        //mark contacts as sent
                        foreach ($quoteCollection as $quote) {
                            $email = $quote->getCustomerEmail();
                            $quoteId = $quote->getId();
                            $storeId = $quote->getStoreId();
                            $collection = $this->getCollection()
                                ->addFieldToFilter('email', $email);
                            if($collection->count()){
                                $send = $collection->getFirstItem();
                            }else{
                                $send = $this;
                                $send->setEmail($email);
                            }
                            $send->setSentAt(Varien_Date::now())
                                ->setIsSent(1)
                                ->setCampaignId($campaignId)->save();
                        }
                    }
                }
            }
        }
        return;
    }

    public function send()
    {
        $helper = Mage::helper('connector');
        $contactModel = Mage::getModel('connector/email_contact');
        $campaigns = array();
        $emailsSend = $this->_getEmailToSent();

        foreach ($emailsSend as $emailSend) {
            $storeId = $emailSend->getStoreId();
            $campaignId = $emailSend->getCampaignId();
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
            $contactId = $helper->getContactId($emailSend->getEmail(), $websiteId);

            $campaignModel = Mage::getModel('connector/connector_campaign');
            $campaignModel->setId($campaignId)
                ->setContact($contactId)
                ->setEmailSendId($emailSend->getEmailSendId());

            if($contactId)
                $campaigns[$emailSend->getCampaignId()][] = $contactId;

        }

        foreach ($campaigns as $campaignId => $contacts) {

            $client = Mage::getModel('connector/connector_api_client');
            $client->setApiUsername($helper->getApiUsername($websiteId))
                ->setApiPassword($helper->getApiPassword($websiteId));

            $result = $client->postCampaignsSend($campaignId, $contacts);

            if(isset($result->message)){
                $errorEmails = $this->getCollection()
                    ->addFieldToFilter('emails_send_ids', $campaign);

                foreach ($contacts as $contact) {


                }
            }
        }
    }

    /**
     * @return mixed
     */
    private function _getEmailToSent(){

        $collection = $this->getCollection();
        $collection->addFieldToFilter('is_sent', array('null' => true));

        $collection->getSelect()->order('campaign_id');

        return $collection->load();
    }

    public function sendEmails($websiteId)
    {

    }

}