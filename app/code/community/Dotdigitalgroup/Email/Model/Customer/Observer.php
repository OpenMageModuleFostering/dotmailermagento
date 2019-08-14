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
        $isSubscribed = $customer->getIsSubscribed();
        try{
            $emailBefore = Mage::getModel('customer/customer')->load($customer->getId())->getEmail();
            $contactModel = Mage::getModel('email_connector/contact')->loadByCustomerEmail($emailBefore, $websiteId);
            //email change detection
            if ($email != $emailBefore) {
                Mage::helper('connector')->log('email change detected : '  . $email . ', after : ' . $emailBefore .  ', website id : ' . $websiteId);
                $enabled = Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_ENABLED, $websiteId);

                if ($enabled) {
                    $client = Mage::helper('connector')->getWebsiteApiClient($websiteId);
                    $subscribersAddressBook = Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SUBSCRIBERS_ADDRESS_BOOK_ID, $websiteId);
                    $response = $client->postContacts($emailBefore);
                    //check for matching email
                    if (isset($response->id)) {
                        if ($email != $response->email) {
                            $data = array(
                                'Email' => $email,
                                'EmailType' => 'Html'
                            );
                            //update the contact with same id - different email
                            $client->updateContact($response->id, $data);

                        }
                        if (!$isSubscribed && $response->status == 'Subscribed') {
                            $client->deleteAddressBookContact($subscribersAddressBook, $response->id);
                        }
                    } elseif (isset($response->message)) {
                        Mage::helper('connector')->log('Email change error : ' . $response->message);
                    }
                }
                $contactModel->setEmail($email);
            }

	        $contactModel->setEmailImported(Dotdigitalgroup_Email_Model_Contact::EMAIL_CONTACT_NOT_IMPORTED)
                ->setCustomerId($customerId)
                ->save();
        }catch(Exception $e){
            Mage::logException($e);
        }

        return $this;
    }

	/**
	 * Add new customers to the automation.
	 * @param Varien_Event_Observer $observer
	 *
	 * @return $this
	 */
	public function handleCustomerRegiterSuccess(Varien_Event_Observer $observer)
	{
		$customer = $observer->getEvent()->getCustomer();
		$email      = $customer->getEmail();
		$websiteId  = $customer->getWebsiteId();

		// send customer to automation mapped
		$this->_postCustomerToAutomation($email, $websiteId);

		return $this;
	}

	/**
	 * Remove the contact on customer delete.
	 *
	 * @param Varien_Event_Observer $observer
	 *
	 * @return $this
	 */
	public function handleCustomerDeleteAfter(Varien_Event_Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $email      = $customer->getEmail();
        $websiteId  = $customer->getWebsiteId();
        /**
         * Remove contact.
         */
        try{
            $contactModel = Mage::getModel('email_connector/contact')->loadByCustomerEmail($email, $websiteId);
            if ($contactModel->getId()) {
                //remove contact
                $contactModel->delete();
            }
            //remove from account
            $client = Mage::helper('connector')->getWebsiteApiClient($websiteId);
            $apiContact = $client->postContacts($email);
            if(! isset($apiContact->message) && isset($apiContact->id))
                $client->DeleteContact($apiContact->id);

        }catch (Exception $e){
            Mage::logException($e);
        }
        return $this;
    }

	private function _postCustomerToAutomation( $email, $websiteId ) {
		/**
		 * Automation Programme
		 */
		$customerAutoCamaignId = Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_AUTOMATION_STUDIO_CUSTOMER, $websiteId);
		Mage::helper( 'connector' )->log( 'AS - customer automation Campaign id : ' . $customerAutoCamaignId );
		if ($customerAutoCamaignId) {
			$client = Mage::helper( 'connector' )->getWebsiteApiClient( $websiteId );
			$apiContact = $client->postContacts($email);

			// get a program by id
			$program = $client->GetProgramById($customerAutoCamaignId);
			/**
			 * id
			 * name
			 * status
			 * dateCreated
			 */
			Mage::helper( 'connector' )->log( 'AS - get customer Program id : ' . $program->id);

			$data = array(
				'Contacts' => array($apiContact->id),
				'ProgramId'   => $program->id,
				'Status'      => $program->status,
				'DateCreated' => $program->dateCreated,
				'AddressBooks' => array()
			);
			$client->PostProgramsEnrolments($data);
		}
	}

    /**
     * Set contact to re-import if registered customer submitted a review
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function reviewSaveAfter(Varien_Event_Observer $observer)
    {
        $dataObject = $observer->getEvent()->getDataObject();
        if($customerId = $dataObject->getCustomerId()){
            $helper = Mage::helper('connector');
            $helper->setConnectorContactToReImport($customerId);
        }
        return $this;
    }
}