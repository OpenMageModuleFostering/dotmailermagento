<?php

class Dotdigitalgroup_Email_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED                      = 'connector_api_settings/api_credentials/enabled';
    const XML_PATH_PASSCODE                     = 'connector_advanced_settings/external/passcode';
    const XML_PATH_LAST_ORDER_ID                = 'connector_data_field_settings/customer_data/last_order_id';
    const XML_PATH_MAPPING_CUSTOMER_ID          = 'connector_data_field_settings/customer_data/customer_id';
    const XML_PATH_MAPPING_ORDER_ID             = 'connector_data_field_settings/customer_data/last_order_no';
    const XML_PATH_API_USERNAME                 = 'connector_api_settings/api_credentials/username';
    const XML_PATH_API_PASSWORD                 = 'connector_api_settings/api_credentials/password';
    const XML_PATH_PAGE_TRACKING_ENABLED        = 'connector_roi_page_tracking_settings/page_tracking/enabled';
    const XML_PATH_ROI_TRACKING_ENABLED         = 'connector_roi_page_tracking_settings/roi_tracking/enabled';

    /**
     * Sync settings page
     */
    const XML_PATH_CUSTOMERS_ADDRESS_BOOK_ID    = 'connector_sync_settings/address_book/customers';
    const XML_PATH_SUBSCRIBERS_ADDRESS_BOOK_ID  = 'connector_sync_settings/address_book/subscribers';
    const XML_PATH_GUEST_ADDRESS_BOOK_ID        = 'connector_sync_settings/address_book/guests';

    const XML_PATH_SYNC_CONTACT_ENABLED         = 'connector_sync_settings/sync/contact_enabled';
    const XML_PATH_SYNC_SUBSCRIBER_ENABLED      = 'connector_sync_settings/sync/subscriber_enabled';
    const XML_PATH_SYNC_ORDER_ENABLED           = 'connector_sync_settings/sync/order_enabled';
    const XML_PATH_SYNC_WISHLIST_ENABLED        = 'connector_sync_settings/sync/wishlist_enabled';


    /**
     * Advanced settings
     */
    const XML_PATH_ADVANCED_DEBUG_ENABLED       = 'connector_advanced_settings/admin/debug_enabled';

    const XML_PATH_SYNC_LIMIT                   = 'connector_advanced_settings/admin/batch_size';

    const XML_PATH_TRANSACTIONAL_DATA_SYNC_LIMIT = 'connector_advanced_settings/sync_limits/orders';

    const XML_PATH_RESOURCE_ALLOCATION          = 'connector_advanced_settings/admin/memory_limit';



    public function isEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @param int/object $website
     * @return mixed
     */
    public function getApiUsername($website = 0)
    {
        $website = Mage::app()->getWebsite($website);

        return $website->getConfig(self::XML_PATH_API_USERNAME);
    }

    public function getApiPassword($website = 0)
    {
        $website = Mage::app()->getWebsite($website);

        return $website->getConfig(self::XML_PATH_API_PASSWORD);
    }

    public function auth($authRequest)
    {
        if($authRequest == Mage::getStoreConfig(self::XML_PATH_PASSCODE)){
            return true;
        }

       // if($this->isEnabledLogs())
            $this->log('Authenication failed : ' . $authRequest);
        exit();
    }

    public function getSubscriberSyncEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_SYNC_SUBSCRIBER_ENABLED);
    }

    public function getMappedCustomerId()
    {
        return Mage::getStoreConfig(self::XML_PATH_MAPPING_CUSTOMER_ID);
    }

    public function getMappedOrderId()
    {
        return Mage::getStoreConfig(self::XML_PATH_MAPPING_ORDER_ID);
    }

    public function getPasscode()
    {
        return Mage::getStoreConfig(self::XML_PATH_PASSCODE);
    }

    public function getLastOrderNo()
    {
        return Mage::getStoreConfig(self::XML_PATH_LAST_ORDER_ID);

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
        return (bool) Mage::getStoreConfig(self::XML_PATH_ADVANCED_DEBUG_ENABLED);
    }

    public function getContactSyncEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_SYNC_CONTACT_ENABLED);
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
        return (bool)Mage::getStoreConfig(self::XML_PATH_PAGE_TRACKING_ENABLED);
    }

    public function getRoiTrackingEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ROI_TRACKING_ENABLED);

    }

    public function getOrderSyncEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_SYNC_ORDER_ENABLED);
    }

    public function getTransactionalSyncLimit()
    {
        return Mage::getStoreConfig(self::XML_PATH_TRANSACTIONAL_DATA_SYNC_LIMIT);
    }

    public function getResourceAllocationEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_RESOURCE_ALLOCATION);
    }

    public function getMappedStoreName($website)
    {
        return  $website->getConfig('connector_data_field_settings/customer_data/store_name');
    }


    public function getContactId($email, $websiteId)
    {
        $client = Mage::getModel('connector/connector_api_client');
        $client->setApiUsername($this->getApiUsername($websiteId))
            ->setApiPassword($this->getApiPassword($websiteId));
        $contactModel = Mage::getModel('connector/email_contact');

        $contact = $contactModel->loadByCustomerEmail($email, $websiteId);

        if($contactId = $contact->getContactId()){
            return $contactId;
        }else{

            $response = $client->postContacts($email);
            if(isset($response->id)){
                $contactId = $response->id;
                $contactModel->setContactId($contactId)->save();
            }
            return $contactId;
        }

    }
    public function getDatafields()
    {
        $dataFields = array(
            'customer_id' => array(
                'name' => 'Customer_ID',
                'type' => 'numeric',
                'visibility' => 'private',
            ),
            'dob' => array(
                'name' => 'DOB',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'title' => array(
                'name' => 'Title',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'website_name' => array(
                'name' => 'Website_Name',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'store_name' => array(
                'name' => 'Store_Name',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'created_at' => array(
                'name' => 'Account_Created_Date',
                'type' => 'date',
                'visibility' => 'private'
            ),
            'last_logged_date' => array(
                'name' => 'Last_Loggedin_Date',
                'type' => 'date',
                'visibility' => 'private'
            ),
            'customer_group' => array(
                'name' => 'Customer_Group',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'billing_address_1' => array(
                'name' => 'Billing_Address_1',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'billing_address_2' => array(
                'name' => 'Billing_Address_2',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'billing_state' => array(
                'name' => 'Billing_State',
                'type' => 'string',
                'visibility' => 'private'
            ),
            'billing_city' => array(
                'name' => 'Billing_City',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'billing_country' => array(
                'name' => 'Billing_Country',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'billing_postcode' => array(
                'name' => 'Billing_Postcode',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'billing_telephone' => array(
                'name' => 'Billing_Telephone',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'delivery_address_1' => array(
                'name' => 'Delivery_Address_1',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'delivery_address_2' => array(
                'name' => 'Delivery_Address_2',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'delivery_state' => array(
                'name' => 'Delivery_State',
                'type' => 'string',
                'visibility' => 'private'
            ),
            'delivery_city' => array(
                'name' => 'Delivery_City',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'delivery_country' => array(
                'name' => 'Delivery_Country',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'delivery_postcode' => array(
                'name' => 'Delivery_Postcode',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'delivery_telephone' => array(
                'name' => 'Delivery_Telephone',
                'type' => 'string',
                'visibility' => 'private',
                'defaultValue' => ''
            ),
            'number_of_orders' => array(
                'name' => 'Number_Of_Orders',
                'type' => 'numeric',
                'visibility' => 'private',
            ),
            'total_spend' => array(
                'name' => 'Total_Spend',
                'type' => 'numeric',
                'visibility' => 'private',
            ),
            'average_order_value' => array(
                'name' => 'Average_Order_Value',
                'type' => 'numeric',
                'visibility' => 'private',
            ),
            'last_order_date' => array(
                'name' => 'Last_Order_Date',
                'type' => 'date',
                'visibility' => 'private',
            ),
            'last_order_id' => array(
                'name' => 'Last_Order_ID',
                'type' => 'numeric',
                'visibility' => 'private',
            )
        );
        return $dataFields;
    }

    /**
     * Default datafields
     * @return array
     */
    public function getDefaultDataFields()
    {
        $dataFields = array(
            array(
                'name' => 'Customer_ID',
                'type' => 'string',
                'visibility' => 'public',
            ),array(
                'name' => 'Order_ID',
                'type' => 'numeric',
                'visibility' => 'public',
            ),array(
                'name' => 'Order_Increment_ID',
                'type' => 'numeric',
                'visibility' => 'public',
            )
        );

        return $dataFields;
    }


    public function getCustomerAddressBook($website)
    {
        $website = Mage::app()->getWebsite($website);
        return $website->getConfig(self::XML_PATH_CUSTOMERS_ADDRESS_BOOK_ID);
    }

    public function getSubscriberAddressBook($website)
    {
        $website = Mage::app()->getWebsite($website);
        return $website->getConfig(self::XML_PATH_SUBSCRIBERS_ADDRESS_BOOK_ID);
    }

    public function getGuestAddressBook($website)
    {
        $website = Mage::app()->getWebsite($website);
        return $website->getConfig(self::XML_PATH_GUEST_ADDRESS_BOOK_ID);
    }


    /**
     *  Sync limit for contacts
     * @return mixed
     */
    public function getSyncLimit()
    {
        return Mage::getStoreConfig(self::XML_PATH_SYNC_LIMIT);
    }


    /**
     * Gets the datafield mapping hash from the system config.
     * @param $website
     * @return array
     */
    public function getMappingHash($website){


        $website = Mage::app()->getWebsite($website);
        $result = array();

        $customerFields = $this->_getCustomerDataFields();


        foreach ($customerFields as $field) {

            $configPath = 'connector_data_field_settings/customer_data/' . $field;

            $result[] = $website->getConfig($configPath);
        }

        return $result;
    }

    private function _getCustomerDataFields(){

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

    public function getWishlistEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_SYNC_WISHLIST_ENABLED);
    }

    public  function allowResourceFullExecution() {

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

    public function countCustomersWithContactId()
    {
        $contactsCollection = Mage::getModel('connector/email_contact')->getCollection()
            ->addFieldToFilter('customer_id', array('notnull' => true))
            ->addFieldToFilter('contact_id', array('notnull' => true));

        return $contactsCollection->getSize();
    }
}
