<?php

class Dotdigitalgroup_Email_Model_Campaign extends Mage_Core_Model_Abstract
{
    //xml path configuration
    const XML_PATH_LOSTBASKET_1_ENABLED      = 'connector_lost_baskets/customers/enabled_1';
    const XML_PATH_LOSTBASKET_2_ENABLED      = 'connector_lost_baskets/customers/enabled_2';
    const XML_PATH_LOSTBASKET_3_ENABLED      = 'connector_lost_baskets/customers/enabled_3';

    const XML_PATH_LOSTBASKET_1_INTERVAL     = 'connector_lost_baskets/customers/send_after_1';
    const XML_PATH_LOSTBASKET_2_INTERVAL     = 'connector_lost_baskets/customers/send_after_2';
    const XML_PATH_LOSTBASKET_3_INTERVAL     = 'connector_lost_baskets/customers/send_after_3';

    const XML_PATH_TRIGGER_1_CAMPAIGN        = 'connector_lost_baskets/customers/campaign_1';
    const XML_PATH_TRIGGER_2_CAMPAIGN        = 'connector_lost_baskets/customers/campaign_2';
    const XML_PATH_TRIGGER_3_CAMPAIGN        = 'connector_lost_baskets/customers/campaign_3';

    const XML_PATH_GUEST_LOSTBASKET_1_ENABLED  = 'connector_lost_baskets/guests/enabled_1';
    const XML_PATH_GUEST_LOSTBASKET_2_ENABLED  = 'connector_lost_baskets/guests/enabled_2';
    const XML_PATH_GUEST_LOSTBASKET_3_ENABLED  = 'connector_lost_baskets/guests/enabled_3';

    const XML_PATH_GUEST_LOSTBASKET_1_INTERVAL = 'connector_lost_baskets/guests/send_after_1';
    const XML_PATH_GUEST_LOSTBASKET_2_INTERVAL = 'connector_lost_baskets/guests/send_after_2';
    const XML_PATH_GUEST_LOSTBASKET_3_INTERVAL = 'connector_lost_baskets/guests/send_after_3';

    const XML_PATH_GUEST_LOSTBASKET_1_CAMPAIGN = 'connector_lost_baskets/guests/campaign_1';
    const XML_PATH_GUEST_LOSTBASKET_2_CAMPAIGN = 'connector_lost_baskets/guests/campaign_2';
    const XML_PATH_GUEST_LOSTBASKET_3_CAMPAIGN = 'connector_lost_baskets/guests/campaign_3';


    //error messages
    const SEND_EMAIL_CONTACT_ID_MISSING = 'Error : missing contact id - will try later to send ';

    public $transactionalClient;

    /**
     * constructor
     */
    public function _construct(){
        parent::_construct();
        $this->_init('email_connector/campaign');

        $this->transactionalClient = Mage::helper('connector/transactional')->getWebsiteApiClient();
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
        $this->setUpdatedAt($now);
        return $this;
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

        if($collection->count()){
            return $collection->getFirstItem();
        }else{
            $this->setQuoteId($quoteId)
                ->setStoreId($storeId);
        }

        return $this;
    }


    /**
     * Sending the campaigns.
     */
    public function sendCampaigns()
    {
        //grab the emails not send
        $emailsToSend = $this->_getEmailCampaigns();
        foreach ($emailsToSend as $campaign) {
            $email = $campaign->getEmail();
            $storeId = $campaign->getStoreId();
            $store = Mage::app()->getStore($storeId);
            $websiteId = $store->getWebsiteId();
            $storeName = $store->getName();
            $websiteName = $store->getWebsite()->getName();
            $campaignId = $campaign->getCampaignId();
            if(!$campaignId){
                $campaign->setMessage('Missing campaign id: ' . $campaignId)
                    ->setIsSent(1)
                    ->save();
                continue;
            }elseif(!$email){
                $campaign->setMessage('Missing email : ' . $email)
                    ->setIsSent(1)
                    ->save();
            }
            try{
                if($campaign->getEventName() == 'Lost Basket'){
                    $client = Mage::helper('connector')->getWebsiteApiClient($websiteId);
                    $contactId = Mage::helper('connector')->getContactId($campaign->getEmail(), $websiteId);
                    Mage::helper('connector')->log($contactId);
                    $response = $client->postCampaignsSend($campaignId, array($contactId));
                    if(isset($response->message)){
                        //update  the failed to send email message
                        $campaign->setMessage($response->message)->setIsSent(1)->save();
                    }
                    $now = Mage::getSingleton('core/date')->gmtDate();
                    //record suscces
                    $campaign->setIsSent(1)
                        ->setMessage(NULL)
                        ->setSentAt($now)
                        ->save();

                    continue;
                }elseif($campaign->getEventName() == 'New Customer Account'){

                    $contactId = Mage::helper('connector/transactional')->getContactId($campaign->getEmail(), $websiteId);
                    $customerId = $campaign->getCustomerId();
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $firstname = $customer->getFirstname();
                    $lastname = $customer->getLastname();
                    $data = array(
                        array(
                            'Key' => 'STORE_NAME',
                            'Value' => $storeName),
                        array(
                            'Key' => 'WEBSITE_NAME',
                            'Value' => $websiteName),
                        array(
                            'Key' => 'FIRSTNAME',
                            'Value' => $firstname),
                        array(
                            'Key' => 'LASTNAME',
                            'Value' => $lastname),
                        array(
                            'Key' => 'CUSTOMER_ID',
                            'Value' => $customerId)
                    );
                    $this->transactionalClient->updateContactDatafieldsByEmail($email, $data);
                    $response = $this->transactionalClient->postCampaignsSend($campaignId, array($contactId));
                    if(isset($response->message)){
                        //update  the failed to send email message
                        $campaign->setMessage($response->message)->setIsSent(1)->save();
                    }else{
                        $now = Mage::getSingleton('core/date')->gmtDate();
                        //record suscces
                        $campaign->setIsSent(1)
                            ->setMessage(NULL)
                            ->setSentAt($now)
                            ->save();
                    }
                }else{
                    // transactional
                    $orderModel = Mage::getModel("sales/order")->loadByIncrementId($campaign->getOrderIncrementId());
                    $contactId = Mage::helper('connector/transactional')->getContactId($campaign->getEmail(), $websiteId);
                    if(is_numeric($contactId)){
                        if($orderModel->getCustomerId()){
                            $firstname = $orderModel->getCustomerFirstname();
                            $lastname = $orderModel->getCustomerLastname();
                        }else{
                            $billing = $orderModel->getBillingAddress();
                            $firstname = $billing->getFirstname();
                            $lastname = $billing->getLastname();
                        }
                        $data = array(
                            array(
                                'Key' => 'STORE_NAME',
                                'Value' => $storeName),
                            array(
                                'Key' => 'WEBSITE_NAME',
                                'Value' => $websiteName),
                            array(
                                'Key' => 'FIRSTNAME',
                                'Value' => $firstname),
                            array(
                                'Key' => 'LASTNAME',
                                'Value' => $lastname),
                            array(
                                'Key' => 'LAST_ORDER_ID',
                                'Value' => $orderModel->getId())
                        );
                        $this->transactionalClient->updateContactDatafieldsByEmail($email, $data);
                    }
                    $response = $this->transactionalClient->postCampaignsSend($campaignId, array($contactId));
                    if(isset($response->message)){
                        //update  the failed to send email message
                        $campaign->setMessage($response->message)->save();
                    }else{
                        $now = Mage::getSingleton('core/date')->gmtDate();
                        //record suscces
                        $campaign->setIsSent(1)
                            ->setMessage(NULL)
                            ->setSentAt($now)
                            ->save();
                    }
                }

            }catch(Exception $e){
                Mage::logException($e);
            }
        }
        return;
    }

    /**
     * @return mixed
     */
    private function _getEmailCampaigns(){
        $emailCollection = $this->getCollection();
        $emailCollection->addFieldToFilter('is_sent', array('null' => true))
            ->addFieldToFilter('campaign_id', array('notnull' => true))
        ;
        $emailCollection->getSelect()->order('campaign_id');

        return $emailCollection;
    }

}