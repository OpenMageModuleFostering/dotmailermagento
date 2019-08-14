<?php

class Dotdigitalgroup_Email_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function isEnabled($website = 0)
    {
        $website = Mage::app()->getWebsite($website);
        return (bool)$website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_ENABLED);
    }

    /**
     * @param int/object $website
     * @return mixed
     */
    public function getApiUsername($website = 0)
    {
        $website = Mage::app()->getWebsite($website);

        return $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_USERNAME);
    }

    public function getApiPassword($website = 0)
    {
        $website = Mage::app()->getWebsite($website);

        return $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_PASSWORD);
    }

    public function auth($authRequest)
    {
        if($authRequest != Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_DYNAMIC_CONTENT_PASSCODE)){
            $this->log('Authenication failed : ' . $authRequest);
            exit();
        }
        return true;
    }

    public function getMappedCustomerId()
    {
        return Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_MAPPING_CUSTOMER_ID);
    }

    public function getMappedOrderId()
    {
        return Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_MAPPING_LAST_ORDER_ID);
    }

    public function getPasscode()
    {
        return Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_DYNAMIC_CONTENT_PASSCODE);
    }

    public function getLastOrderId()
    {
        return Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_MAPPING_LAST_ORDER_ID);

    }

    public function log($data, $level = Zend_Log::DEBUG, $filename = 'api.log')
    {
        if($this->getDebugEnabled()){
            $filename = 'connector_' . $filename;

            Mage::log($data, $level, $filename, $force = true);
        }
    }

    public function getDebugEnabled()
    {
        return (bool) Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_ADVANCED_DEBUG_ENABLED);
    }

    public function getConnectorVersion()
    {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();
        if(isset($modules['Dotdigitalgroup_Email'])){
            $moduleName = $modules['Dotdigitalgroup_Email'];
            return $moduleName->version;
        }
        return '';
    }


    public function getPageTrackingEnabled()
    {
        return (bool)Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_PAGE_TRACKING_ENABLED);
    }

    public function getRoiTrackingEnabled()
    {
        return (bool)Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_ROI_TRACKING_ENABLED);
    }

    public function getResourceAllocationEnabled()
    {
        return (bool)Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_RESOURCE_ALLOCATION);
    }

    public function getMappedStoreName($website)
    {
        $mapped = $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_MAPPING_CUSTOMER_STORENAME);
        $storeName = ($mapped)? $mapped : '';
        return  $storeName;
    }

    /**
     * Get the contact id for the custoemer based on website id.
     * @param $email
     * @param $websiteId
     * @return bool
     */
    public function getContactId($email, $websiteId)
    {
        $client = $this->getWebsiteApiClient($websiteId);
        $response = $client->postContacts($email);
        return $response->id;
    }

    public function getCustomerAddressBook($website)
    {
        $website = Mage::app()->getWebsite($website);
        return $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMERS_ADDRESS_BOOK_ID);
    }

    public function getSubscriberAddressBook($website)
    {
        $website = Mage::app()->getWebsite($website);
        return $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SUBSCRIBERS_ADDRESS_BOOK_ID);
    }

    public function getGuestAddressBook($website)
    {
        $website = Mage::app()->getWebsite($website);
        return $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ADDRESS_BOOK_ID);
    }

    /**
     * Gets the datafield mapping hash from the system config.
     * @param $website
     * @return array
     */
    public function getMappingHash($website)
    {
        $result = array();
        $website = Mage::app()->getWebsite($website);
        $customerFields = $this->getCustomerDataFields();
        foreach ($customerFields as $field) {
            $path = 'connector_data_mapping/customer_data/' . $field;
            $result[] = $website->getConfig($path);
        }

        return $result;
    }

    public function getCustomerDataFields(){

        return  array(
            'title',
            'firstname',
            'lastname',
            'dob',
            'gender',
            'website_name',
            'store_name',
            'created_at',
            'last_logged_date',
            'customer_group',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_country',
            'billing_postcode',
            'billing_telephone',
            'delivery_address_1',
            'delivery_address_2',
            'delivery_city',
            'delivery_country',
            'delivery_postcode',
            'delivery_telephone',
            'number_of_orders',
            'average_order_value',
            'total_spend',
            'last_order_date',
            'last_order_id',
            'customer_id',
        );
    }


    /**
     * @return $this
     */
    public  function allowResourceFullExecution()
    {
        if($this->getResourceAllocationEnabled()){

            /* it may be needed to set maximum execution time of the script to longer,
             * like 60 minutes than usual */
            set_time_limit(7200);

            /* and memory to 512 megabytes */
            ini_set('memory_limit', '512M');
        }
        return $this;
    }
    public function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    /**
     * @return string
     */
    public function getStringWebsiteApiAccounts()
    {
        $accounts = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $websiteId = $website->getId();
            $apiUsername = $this->getApiUsername($website);
            $accounts[$apiUsername] = $apiUsername . ', websiteId: ' . $websiteId . ' name ' . $website->getName();
        }
        return implode('</br>', $accounts);
    }

    public function getCustomAttributes($website = 0)
    {
        $website = Mage::app()->getWebsite($website);
        return unserialize($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_MAPPING_CUSTOM_DATAFIELDS));
    }

    /**
     * @param $path
     * @param null|string|bool|int|Mage_Core_Model_Website $websiteId
     * @return mixed
     */
    public function getWebsiteConfig($path, $websiteId = 0)
    {
        $website = Mage::app()->getWebsite($websiteId);
        return $website->getConfig($path);
    }

    /**
     * Api client by website.
     * @param int $website
     * @return Dotdigitalgroup_Email_Model_Apiconnector_Client
     */
    public function getWebsiteApiClient($website = 0)
    {
        $client = Mage::getModel('email_connector/apiconnector_client');
        $client->setApiUsername($this->getApiUsername($website))
                ->setApiPassword($this->getApiPassword($website));
        
        return $client;
    }
}
