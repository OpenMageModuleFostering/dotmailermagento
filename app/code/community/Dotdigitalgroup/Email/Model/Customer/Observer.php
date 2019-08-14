<?php

class Dotdigitalgroup_Email_Model_Customer_Observer
{
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
            $contactModel = Mage::getModel('email_connector/contact')->loadByCustomerEmail($email, $websiteId);
            $contactModel->setEmailImported(Dotdigitalgroup_Email_Model_Contact::EMAIL_CONTACT_NOT_IMPORTED)
                ->setCustomerId($customerId)
                ->save();
        }catch(Exception $e){
            Mage::logException($e);
        }

        return $this;
    }
}