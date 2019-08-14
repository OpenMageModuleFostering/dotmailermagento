<?php

class Dotdigitalgroup_Email_Model_Newsletter_Observer
{

    public function handleNewsletterSubscriberSave(Varien_Event_Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $storeId = $subscriber->getStoreId();
        $email   = $subscriber->getEmail();
        $subscriberStatus = $subscriber->getSubscriberStatus();
        $contactId = '';
        $helper = Mage::helper('connector');
        $websiteId = Mage::app()->getStore($subscriber->getStoreId())->getWebsiteId();
        $contactEmail = Mage::getModel('email_connector/contact')->loadByCustomerEmail($email, $websiteId);
        try{
            /**
             * Subscribe a contact
             */
            if($subscriberStatus == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
                $contactEmail->setSubscriberStatus($subscriberStatus)
                    ->setIsSubscriber(1);

                /**
                 * Resubscribe suppressed contacts.
                 */
                $client = Mage::helper('connector')->getWebsiteApiClient($websiteId);
                $apiContact = $client->getContactByEmail($email);
                if(isset($apiContact->status) && $apiContact->status == 'Suppressed'){
                    $client->PostContactsResubscribe($email, $apiContact);
                }
                $client->PostContactsResubscribe($email, $apiContact);
                $contactEmail->setSuppressed(null);
            }else{
                /**
                 * Unsubscribe contact
                 */
                $client = Mage::helper('connector')->getWebsiteApiClient($websiteId);
                if(!$contactEmail->getContactId()){
                    //if contact id is not set get the contact_id
                    $result = $client->postContacts($email);
                    $contactId = $result->id;
                }else{
                    $contactId = $contactEmail->getContactId();
                }
                if(is_numeric($contactId)){
                    $client->deleteAddressBookContact($helper->getSubscriberAddressBook($websiteId), $contactId);
                }else{
                    Mage::helper('connector')->log('CONTACT ID IS EMPTY : ' . $contactId . ' email : ' . $email);
                }
                $contactEmail->setIsSubscriber(null);
            }

            $contactEmail->setStoreId($storeId)
                ->setContactId($contactId)
                ->save();

        }catch(Exception $e){
            Mage::logException($e);
        }
        return $this;
    }
}