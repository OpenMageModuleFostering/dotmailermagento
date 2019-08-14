<?php

class Dotdigitalgroup_Email_Model_Newsletter_Subscriber
{
    const STATUS_SUBSCRIBED     = 1;
    const STATUS_NOT_ACTIVE     = 2;
    const STATUS_UNSUBSCRIBED   = 3;
    const STATUS_UNCONFIRMED    = 4;

    protected $_start;

    /**
     * SUBSCRIBER SYNC.
     * @return $this
     */
    public function sync()
    {
        /** @var Dotdigitalgroup_Email_Helper_Data $helper */
        $helper = Mage::helper('connector');
        $helper->log('---------------------- Start subscriber sync -------------------');
        $this->_start = microtime(true);

        foreach (Mage::app()->getWebsites(true) as $website) {
            if (Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_SUBSCRIBER_ENABLED, $website))
                $this->exportSubscribersPerWebsite($website);
        }

        return $this;
    }

	/**
	 * Export subscriber per website.
	 *
	 * @param $website
	 */
	public function exportSubscribersPerWebsite($website)
    {
        $updated = 0;
        $helper = Mage::helper('connector');
        $fileHelper = Mage::helper('connector/file');
        $limit = $helper->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_LIMIT, $website);
        $subscribers = Mage::getModel('email_connector/contact')->getSubscribersToImport($website, $limit);
        if (count($subscribers)) {
            $client = Mage::helper('connector')->getWebsiteApiClient($website);
            $subscribersFilename = strtolower($website->getCode() . '_subscribers_' . date('d_m_Y_Hi') . '.csv');
            //get mapped storename
            $subscriberStorename = $helper->getMappedStoreName($website);
            //file headers
            $fileHelper->outputCSV($fileHelper->getFilePath($subscribersFilename), array('Email', 'emailType', $subscriberStorename));
            foreach ($subscribers as $subscriber) {
                try{
                    $email = $subscriber->getEmail();
                    $subscriber->setSubscriberImported(1)->save();
                    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                    $storeName = Mage::app()->getStore($subscriber->getStoreId())->getName();
                    // save data for subscribers
                    $fileHelper->outputCSV($fileHelper->getFilePath($subscribersFilename), array($email, 'Html', $storeName));
                    $updated++;
                }catch (Exception $e){
                    Mage::logException($e);
                }
            }
            $helper->log('Subscriber filename: ' . $subscribersFilename);
            //Add to subscriber address book
            $client->postAddressBookContactsImport($subscribersFilename, $helper->getSubscriberAddressBook($website));
            $fileHelper->archiveCSV($subscribersFilename);
        }
    }

    /**
     * Unsubscribe suppressed contacts.
     * @param bool $force set 10years old
     * @return mixed
     */
    public function unsubscribe($force = false)
    {
        $result['customers'] = 0;
        $limit = 5;
        $max_to_select = 1000;
        $date = Zend_Date::now()->subHour(1);
        $helper = Mage::helper('connector');
        // force sync all customers
        if($force)
            $date = $date->subYear(10);
        // datetime format string
        $dateString = $date->toString(Zend_Date::W3C);

        /**
         * 1. Sync all suppressed for each store
         */
            foreach (Mage::app()->getWebsites(true) as $website) {
                if (! Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_ENABLED, $website))
                    continue;
                $contacts = array();
                $skip = $i = 0;
                $client = Mage::helper('connector')->getWebsiteApiClient($website);
                //there is a maximum of request we need to loop to get more suppressed contacts
                for ($i=0; $i<= $limit;$i++) {
                    $apiContacts = $client->getContactsSuppressedSinceDate($dateString, $max_to_select , $skip);
                    // skip no more contacts
                    if(empty($apiContacts))
                        break;
                    $contacts = array_merge($contacts, $apiContacts);
                    $skip += 1000;
                }
                $subscriberBookId = $helper->getSubscriberAddressBook($website);
                // suppressed contacts to unsubscibe
                foreach ($contacts as $apiContact) {
                    if (isset($apiContact->suppressedContact)) {
                        $suppressedContact = $apiContact->suppressedContact;
                        $email = $suppressedContact->email;
                        $contactId = $suppressedContact->id;
                        try{
                            /**
                             * 2. Unsubscribe customer.
                             */
                            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                            if ($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                                $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                                $subscriber->save();
                                // remove from subscriber address-book
                                $client->deleteAddressBookContact($subscriberBookId, $contactId);
                            }
                            //mark contact as suppressed and unsubscribe
                            $contactCollection = Mage::getModel('email_connector/contact')->getCollection()
                                ->addFieldToFilter('email', $email)
                                ->addFieldToFilter('website_id', $website->getId());
                            //unsubscribe from the email contact table.
                            foreach ($contactCollection as $contact) {
                                $contact->setIsSubscriber(null)
                                    ->setSuppressed(1)->save();
                            }
                        }catch (Exception $e){
                            Mage::logException($e);
                        }
                    }
                }
            }

        return $result;
    }
}