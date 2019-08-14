<?php

class Dotdigitalgroup_Email_Model_Newsletter_Subscriber
{

    const STATUS_SUBSCRIBED     = 1;
    const STATUS_NOT_ACTIVE     = 2;
    const STATUS_UNSUBSCRIBED   = 3;
    const STATUS_UNCONFIRMED    = 4;

    private $suppressedContacts = array();

    protected $_countSubscribers = 0;


    protected $_start;



    public function sync()
    {
        /** @var Dotdigitalgroup_Email_Helper_Data $helper */
        $helper = Mage::helper('connector');
        $helper->log('Start subscriber sync..');
        $this->_start = microtime(true);

        foreach(Mage::app()->getWebsites() as $website){
            $this->_exportSubscribersPerWebsite($website);
        }
        $helper->log('Total time for sync : ' . gmdate("H:i:s", microtime(true) - $this->_start));

        return $this;
    }

    public function _exportSubscribersPerWebsite($website)
    {
        $helper = Mage::helper('connector');
        $updated = 0;
        $fileHelper = Mage::helper('connector/file');
        $client = Mage::getModel('connector/connector_api_client')
            ->setApiUsername($helper->getApiUsername($website))
            ->setApiPassword($helper->getApiPassword($website));

        $subscribersFilename = strtolower($website->getCode() . '_subscribers_' . date('d_m_Y_Hi') . '.csv');
        $helper->log('Subscribers file: ' . $subscribersFilename);

        //get mapped storename
        $subscriberStorename = $helper->getMappedStoreName($website);
        //subscriber file headers
        $fileHelper->outputCSV($fileHelper->getFilePath($subscribersFilename), array('Email', 'emailType', $subscriberStorename));

        $subscribers = Mage::getModel('connector/email_contact')->getSubscribersToImport($helper->getSyncLimit());

        foreach ($subscribers as $contact) {

            try{
                $email = $contact->getEmail();
                $contact->setSubscriberImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_IMPORTED)->save();
            }catch (Exception $e){
                $helper->log($e->getMessage());
            }
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            $storeId = $subscriber->getStoreId();
            $storeName = Mage::app()->getStore($storeId)->getName();
            // save data for subscribers
            $fileHelper->outputCSV($fileHelper->getFilePath($subscribersFilename), array($email, 'Html', $storeName));
            $updated++;

        }
        if($updated){
            //Add to subscriber address book
            $client->postAddressBookContactsImport($subscribersFilename, $helper->getSubscriberAddressBook($website));
        }
        $this->_countSubscribers += $updated;
        $fileHelper->archiveCSV($subscribersFilename);
    }

    public function unsubscribe($force = false)
    {
        $result['customers'] = 0;
        $date = new Zend_Date();
        $date = $date->subHour(1);
        $client = Mage::getModel('connector/connector_api_client');
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

            $client->setApiUsername($helper->getApiUsername($website));
            $client->setApiPassword($helper->getApiPassword($website));
            $subscriberBookId = $helper->getSubscriberAddressBook($website);
            $contacts = $client->getContactsSuppressedSinceDate($dateString);

            if(! empty($contacts)){
                foreach ($contacts as $suppressed){
                    if(isset($suppressed->suppressedContact)){

                        $email = $suppressed->suppressedContact->email;
                        $contactId = $suppressed->suppressedContact->id;

                        try{
                            /**
                             * 2. Unsubscribe customer
                             */
                            $newsletterModel = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                            if($newsletterModel->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
                                $unsubscribe = $newsletterModel->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                                $unsubscribe->save();
                                // remove from subscriber address-book
                                $client->deleteAddressBookContact($website->getConfig(), $contactId);
                                $this->suppressedContacts[$newsletterModel->getSubscriberEmail($subscriberBookId)] = $newsletterModel->getSubscriberEmail();
                            }
                            //mark contact as suppressed and unsubscribe
                            $contactCollection = Mage::getModel('connector/email_contact')->getCollection()
                                ->addFieldToFilter('email', $email);

                            foreach ($contactCollection as $contact) {
                                $contact->setIsSubscriber(null)
                                    ->setSuppressed(1)->save();
                            }
                        }catch (Exception $e){
                            Mage::helper('connector')->log($e->getMessage());
                        }
                    }
                }
            }
        }
        return $result;
    }
}