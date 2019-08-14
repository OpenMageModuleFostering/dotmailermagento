<?php

class Dotdigitalgroup_Email_Helper_Transactional extends Mage_Core_Helper_Abstract
{
    const XML_PATH_TRANSACTIONAL_API_ENABLED                    = 'connector_transactional_emails/credentials/enabled';
    const XML_PATH_TRANSACTIONAL_API_USERNAME                   = 'connector_transactional_emails/credentials/api_username';
    const XML_PATH_TRANSACTIONAL_API_PASSWORD                   = 'connector_transactional_emails/credentials/api_password';
    const XML_PATH_TRANSACTIONAL_MAPPING                        = 'connector_transactional_emails/email_mapping/';


    /**
	 * Get the api enabled.
	 *
	 * @return mixed
	 */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_TRANSACTIONAL_API_ENABLED);
    }

    /**
	 * Get api username.
	 *
	 * @param int $website
	 *
	 * @return mixed
	 * @throws Mage_Core_Exception
	 */
    public function getApiUsername($website = 0)
    {
        $website = Mage::app()->getWebsite($website);

        return $website->getConfig(self::XML_PATH_TRANSACTIONAL_API_USERNAME);
    }

    /**
	 * Get api password.
	 *
	 * @param int $website
	 *
	 * @return mixed
	 * @throws Mage_Core_Exception
	 */
    public function getApiPassword($website = 0)
    {
        $website = Mage::app()->getWebsite($website);

        return $website->getConfig(self::XML_PATH_TRANSACTIONAL_API_PASSWORD);
    }

    /**
	 * Website by name.
	 * @param $websiteName
	 *
	 * @return Varien_Object
	 */
    public function getWebsiteByName($websiteName)
    {
        $website = Mage::getModel('core/website')->getCollection()
            ->addFieldToFilter('code', $websiteName)->getFirstItem();

        return $website;
    }

    /**
     * Check if the template is mapped
     * @param $templateId
     * @return bool
     */
    public function isMapped($templateId)
    {
        $path = self::XML_PATH_TRANSACTIONAL_MAPPING . $templateId;

        return Mage::getStoreConfig($path);
    }

    /**
     * transactional mapped campaign id
     * @param $templateId
     * @param int $storeId
     * @return mixed
     */
    public function getTransactionalCampaignId($templateId, $storeId = 0)
    {
        $path = self::XML_PATH_TRANSACTIONAL_MAPPING . $templateId;

        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Get the contact id for the custoemer based on website id.
     * @param $email
     * @param $websiteId
     * @return string contact_id
     */
    public function getContactId($email, $websiteId)
    {
        $client = $this->getWebsiteApiClient($websiteId);
        $response = $client->postContacts($email);
        if (isset($response->message))
            return $response->message;
        return $response->id;
    }

    /**
	 * Update contact default datafields.
	 *
	 * @param $contacData
	 */
    public function updateContactData($contacData)
    {
        $client = $this->getWebsiteApiClient($contacData->getWebsite());
        $email  = $contacData->getCustomerEmail();
        /**
         * Transactional account data default datafields.
         */
        $data = array(
            array(
                'Key' => 'LAST_ORDER_ID',
                'Value' => $contacData->getOrderId()),
            array(
                'Key' => 'CUSTOMER_ID',
                'Value' => $contacData->getCustomerId()),
            array(
                'Key' => 'ORDER_INCREMENT_ID',
                'Value' => $contacData->getOrderIncrementId()),
            array(
                'Key' => 'WEBSITE_NAME',
                'Value' => $contacData->getWebsiteName()),
            array(
                'Key' => 'STORE_NAME',
                'Value' => $contacData->getStoreName()),
            array(
                'Key' => 'LAST_ORDER_DATE',
                'Value' => $contacData->getOrderDate())
        );
        $client->updateContactDatafieldsByEmail($email, $data);
    }

    /**
     * Api client by website.
     * @param int $website
     * @return Dotdigitalgroup_Email_Model_Apiconnector_Client
     */
    public function getWebsiteApiClient($website = 0)
    {
        $client = Mage::getModel('email_connector/apiconnector_client');
        $website = Mage::app()->getWebsite($website);
        if ($website) {
            $client->setApiUsername($this->getApiUsername($website))
                ->setApiPassword($this->getApiPassword($website));
        }
        return $client;
    }

}