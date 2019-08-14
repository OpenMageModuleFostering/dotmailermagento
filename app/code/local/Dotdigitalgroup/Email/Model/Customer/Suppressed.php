<?php

class Dotdigitalgroup_Email_Model_Customer_Suppressed extends Dotdigitalgroup_Email_Model_Api_Rest
{

    private $suppressedContacts = array();

    public function unsubscribe($force = false)
    {
        // result of sync
        $result = array('errors' => false, 'message' => '', 'customers');
        $result['customers'] = 0;
        $date = new Zend_Date();

        /**
         * 1. calculate from frequency
         */
        if(! $force){
            $frequency = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CUSTOMERS_SUPPRESSED_INTERVAL);
            // get the frequency of sinse date
            switch($frequency){
                case('H'):
                    $date = $date->subHour(1);
                    break;
                case('D'):
                    $date = $date->subDay(1);
                    break;
                case('W'):
                    $date = $date->subWeek(1);
                    break;
                case('M'):
                    $date = $date->subMonth(1);
                    break;
            }
        }else{

            // force sync all customers
            $date = $date->subYear(Dotdigitalgroup_Email_Model_Customer_Customer::FORCE_CUSTOMERS_YEARS);
        }

        // datetime format string
        $dateString = $date->toString(Zend_Date::W3C);
        /**
         * Sync all suppressed for each store
         */
        foreach (Mage::app()->getWebsites(true) as $website) {
            $this->_api_user     = $website->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME);
            $this->_api_password = $website->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD);
            $contacts = $this->getSuppressedSince($dateString);
            if(! empty($contacts)){
                foreach ($contacts as $suppressed){
                    $contactEmail = false;
                    if(isset($suppressed->suppressedContact)){

                        $contactEmail = $suppressed->suppressedContact->email;
                        $contactId = $suppressed->suppressedContact->id;
                    }

                    if($contactEmail){
                        try{
                            /**
                             * 3. Unsubscribe customer
                             */
                            $newsletterModel = Mage::getModel('newsletter/subscriber')->loadByEmail($contactEmail);
                            if($newsletterModel->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
                                $unsubscribe = $newsletterModel->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
                                $unsubscribe->save();
                                // remove from subscriber address-book
                                $this->deleteAddressBookContact($website->getConfig(Dotdigitalgroup_Email_Model_Newsletter_Subscriber::XML_PATH_SUBSCRIBERS_ADDRESS_BOOK_ID), $contactId);
                                $this->suppressedContacts[$newsletterModel->getSubscriberEmail()] = $newsletterModel->getSubscriberEmail();
                            }
                        }catch (Exception $e){
                            $result['errors'] = true;
                            $result['message'] = 'Error Saving Customer!';
                        }
                    }
                }
            }
        }
        $result['customers'] = count($this->suppressedContacts);

        return $result;
    }

}