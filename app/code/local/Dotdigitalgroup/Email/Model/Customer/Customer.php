<?php

class Dotdigitalgroup_Email_Model_Customer_Customer extends Dotdigitalgroup_Email_Model_Api_Rest
{
    // xml configuration
    const XML_PATH_CONNECTOR_API_USERNAME        = 'connector_api_settings/api_credentials/username';
    const XML_PATH_CONNECTOR_API_PASSWORD        = 'connector_api_settings/api_credentials/password';
    const XML_PATH_CUSTOMER_SYNC_ENABLE          = 'connector_sync_settings/sync_customer_schedule/enabled';
    const XML_PATH_CUSTOMER_SYNC_LIMIT           = 'connector_advanced_settings/sync_limits/contact';
    const XML_PATH_CUSTOMER_SUPPRESSED_ENABLE    = 'connector_sync_settings/sync_suppressed_schedule/enabled';
    const XML_PATH_CUSTOMERS_ADDRESS_BOOK_ID     = 'connector_data_field_settings/address_book/customers';
    const XML_PATH_CUSTOMERS_SUPPRESSED_INTERVAL = 'connector_sync_settings/sync_suppressed_schedule/frequency';
    const XML_PATH_GUEST_ADDRESS_BOOK_ID         = 'connector_data_field_settings/address_book/guest';
    const XML_PATH_CUSTOMER_WISHLIST_ENABLED     = 'connector_sync_settings/transactional_data/wishlist_enabled';

    const FORCE_CUSTOMERS_YEARS = '10';

    protected  $_customers_address_book_id;
    protected  $_customers = array();
    protected  $_mapping_hash;
    protected  $_website; // website model
    private $countSubscribers = 0;
    private $countCustomers = 0;

    protected $accounts = array();// api accounts
    protected $wishlists = array();// wishlists
    protected $_proccessing;
    protected $_websiteId;
    public $apiLimit = 450;

    public function sync()
    {
        /** @var Dotdigitalgroup_Email_Helper_Data $helper */
        $helper = Mage::helper('connector');
        $helper->log('Start customer sync..', null, $this->_log_filename);
        $result = array('error' => false, 'message' => "Done.");

        // check for default settings to use memory limit
        if(Mage::getStoreConfig('connector_advanced_settings/admin/memory_limit'))
            $this->allowResourceFullExecution();

        // get all websites
        foreach(Mage::app()->getWebsites() as $website){

            // skip any actions if sync disabled
            if(! $website->getConfig(self::XML_PATH_CUSTOMER_SYNC_ENABLE))
                continue;
            //API credentials
            $this->_api_user     = $website->getConfig(self::XML_PATH_CONNECTOR_API_USERNAME);
            $this->_api_password = $website->getConfig(self::XML_PATH_CONNECTOR_API_PASSWORD);

            if(strlen($this->_api_user) > 10 && strlen($this->_api_password)){

                $result = $this->exportCustomersForWebsite($website);
                if($website->getConfig(self::XML_PATH_CUSTOMER_WISHLIST_ENABLED)){
                    $this->exportWishlistsForWebsite($website);
                }
            }else{
                $result['error'] = true;
                $message = 'The Credentials For Website :' . $website->getCode() . ' is not set!';
                $result['message'] = $message;
                $helper->log($message, null, $this->_log_filename);
            }
        }
        //set result check
        if($this->countCustomers != 0){

            $message = 'Total Exported : ' . $this->countCustomers . ' Customers, ' . $this->countSubscribers . ' Subscribers.';

            $result['message'] = $message;
            $helper->log($message, null, $this->_log_filename);
        }else{
            $result['error'] = true;
            $message = 'Check the logs for more information, the number of updated customers is : 0.';
            $result['message'] = $message;
        }

        $helper->log('Customer sync end!', null, $this->_log_filename);
        return $result;
    }

    /**
     * Get the customer for the website
     * @param bool $websiteId
     * @return mixed
     */
    public function getContactsCustomers($websiteId = false)
    {
        /** @var Mage_Customer_Model_Customer $customerCollection */
        $customerCollection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('dotmailer_contact_id')
            ->addAttributeToFilter('dotmailer_contact_id', array('notnull' => true), 'left')
        ;
        if($websiteId !== false)
            $customerCollection->addAttributeToFilter('website_id', $websiteId);

        return $customerCollection;
    }

    private function countNumOfContacts($contacts)
    {
        $total = 0;
        // Get contacts number with contact id
        foreach ($contacts as $contact) {
            if($contact->getDotmailerContactId())
                $total++;
        }
        unset($contacts);
        return $total;
    }

    public function syncContacts()
    {
        $updated = 0;
        /**
         * Customers at website level
         */
        foreach (Mage::app()->getWebsites(true) as $website) {
            $websiteId = $website->getId();
            $missingContacts = $this->getMissingContacts($websiteId, $this->apiLimit);
            Mage::helper('connector')->log('API LIMIT : ' . $this->apiLimit, null, 'api.log');
            Mage::helper('connector')->log($missingContacts->count(), null, 'api.log');
            if(count($missingContacts) == 0)
                continue;
            //save customer and trigger the obsever
            foreach($missingContacts as $customer){
                try{
                    $customer->save();
                    $updated++;
                    //limit the number of updated contacts
                    if($this->apiLimit == $updated){
                        return $updated;
                    }
                }catch (Exception $e){
                    Mage::logException($e);
                }
            }
        }
        return $updated;
    }
    public function getMissingContacts($websiteId = null, $limit = 200)
    {
        $customerCollection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('dotmailer_contact_id')
            ->addAttributeToFilter('dotmailer_contact_id', array('null' => true), 'left')
            ->setPageSize($limit)
        ;
        if($websiteId !== null)
            $customerCollection->addAttributeToFilter('website_id', $websiteId);

        return $customerCollection;
    }
    public function getTotalNumberCustomers()
    {
        $customers = Mage::getModel('customer/customer')->getCollection();
        $totalCustomers = $customers->getSize();

        return $totalCustomers;
    }

    public function countSkipContacts($contacts = false, $websiteId)
    {
        if($contacts)
            $numContactIds = $this->countNumOfContacts($contacts);
        else
            $numContactIds = count($this->getContactsCustomers($websiteId));


        return $numContactIds;
    }

    /**
     * @param Mage_Core_Model_Website $website
     * @return $this
     */
    public function exportCustomersForWebsite(Mage_Core_Model_Website $website)
    {
        $websiteCode = $website->getCode();
        $websiteId = $website->getId();
        $customer_filename   = $websiteCode . '_' . $this->_customers_file_slug   . '_' . date('d-M-Y_hms') . '.csv';
        $subscriber_filename = $websiteCode . '_' . $this->_subscribers_file_slug . '_' . date('d-M-Y_hms') . '.csv';
        $helper = Mage::helper('connector');

        $helper->log('customer filename : ' . $customer_filename . ', subscriber filename : ' . $subscriber_filename, null, $this->_log_filename);

        $pageNum = 1;
        $pageSize = $website->getConfig('connector_advanced_settings/admin/batch_size');
        if($pageSize == '') $pageSize = 5000;
        $this->fileHelper = Mage::helper('connector/file');

        do{
            $time_start = microtime(true);

            $currentBatch = $this->getCustomersToExport($pageSize, $pageNum, $this->getMappingHash($website), $website->getId());
            $condition = count($currentBatch);
            //write the csv headers
            if($pageNum == 1 && count($currentBatch)){
                $this->fileHelper->outputCSV($this->fileHelper->getFilePath($customer_filename), $this->getCsvHeaderArray($website));
                $this->fileHelper->outputCSV($this->fileHelper->getFilePath($subscriber_filename), array('Email', 'emailType'));
            }
            foreach ($currentBatch as $customer) {

                /**
                 * Send wishlist as transactional data
                 */
                if($website->getConfig(self::XML_PATH_CUSTOMER_WISHLIST_ENABLED)){
                   $this->setWishlists($customer, $website);
                }
                // check if customer is subscribed
                if($customer->isSubscribed()){
                    // save data for subscribers
                    $this->fileHelper->outputCSV($this->fileHelper->getFilePath($subscriber_filename), array($customer->getEmail(), 'Html'));
                    $this->countSubscribers++;
                }

                // save data for csutomers
                $this->fileHelper->outputCSV($this->fileHelper->getFilePath($customer_filename), $customer->toCSVArray());
                $this->countCustomers++;
            }
            $time_end = microtime(true);
            $time_in_seconds = $time_end - $time_start;

            $helper->log('-----------------------------------------------execution time :' . gmdate("H:i:s", $time_in_seconds), null, $this->_log_filename);
            $pageNum++;
            unset($currentBatch);

        }while( $condition == $pageSize );

        $storeIds = Mage::getModel('core/website')->load($websiteId)
            ->getStoreIds();
        $subscriberModel = new Dotdigitalgroup_Email_Model_Newsletter_Subscriber();
        $subscribersNotCustomers = $subscriberModel->getSubscribersNotCustomers($storeIds);

        foreach ($subscribersNotCustomers as $one) {
            $this->fileHelper->outputCSV($this->fileHelper->getFilePath($subscriber_filename), array($one->getSubscriberEmail()));
            $this->countSubscribers++;
        }

        /**
         * Save customers and subscribers to address books
         */
        $this->postAddressBookContactsImport($customer_filename,   $website->getConfig(self::XML_PATH_CUSTOMERS_ADDRESS_BOOK_ID));
        $this->postAddressBookContactsImport($subscriber_filename, $website->getConfig(Dotdigitalgroup_Email_Model_Newsletter_Subscriber::XML_PATH_SUBSCRIBERS_ADDRESS_BOOK_ID));

        //If successful, archive the CSV file and Log something in the Magento Log - success/failure
        if(file_exists($this->fileHelper->getFilePath($customer_filename)) && file_exists($this->fileHelper->getFilePath($subscriber_filename))){
            $this->fileHelper->archiveCSV($customer_filename);
            $this->fileHelper->archiveCSV($subscriber_filename);
        }
        return $this;

    }

    private function exportWishlistsForWebsite(Mage_Core_Model_Website $website){

        $this->_api_user = $website->getConfig(self::XML_PATH_CONNECTOR_API_USERNAME);
        $this->_api_password = $website->getConfig(self::XML_PATH_CONNECTOR_API_PASSWORD);
        //wishlists for the website key
        if(isset($this->wishlists[$website->getId()])){

            //send wishlists as transactional data
            $wishlists = $this->wishlists[$website->getId()];

            $this->postContactsTransactionalDataImport($collectionName = 'Wishlist', $wishlists);
        }
    }

    public function getCustomersToExport($pageSize = 0, $pageNum = 1, $mappingHash, $websiteId)
    {
        // the filtering and aggregation of all the customer data here
        $customers = array();
        $helper = Mage::helper('connector');

        $customerCollection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addAttributeToSelect('dob')
            ->addAttributeToSelect('gender')
            ->addAttributeToSelect('prefix')
            ->addAttributeToSelect('website_id')
            ->addAttributeToSelect('store_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('last_logged_in')
            ->addAttributeToSelect('group_id')
            ->addAttributeToSelect('dob')
            ->addAttributeToSelect('dotmailer_contact_id')

            ->addAttributeToFilter('website_id', $websiteId)

            ->joinAttribute('billing_street', 'customer_address/street', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_code', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('shipping_street', 'customer_address/street', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_city', 'customer_address/city', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_country_code', 'customer_address/country_id', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_postcode', 'customer_address/postcode', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_telephone', 'customer_address/telephone', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_region', 'customer_address/region', 'default_shipping', null, 'left')
            ->joinTable('newsletter/subscriber','subscriber_email=email',array('subscriber_status' => 'subscriber_status'), null, 'left')

        ;
        if( $pageSize ){
            $customerCollection->setPage($pageNum, $pageSize);
        }

        $customer_log = Mage::getSingleton('core/resource')->getTableName('log_customer');
        $sales_flat_order_grid = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');
        $sales_flat_order = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');

        // get the last login from the log_customer table
        $customerCollection->getSelect()->columns(array('last_logged_in' => new Zend_Db_Expr ("(SELECT login_at
                    FROM  $customer_log
                    WHERE customer_id =e.entity_id
                    ORDER BY log_id DESC
                    LIMIT 1)")));

        // customer order information
        $alias = 'subselect';
        $subselect = Mage::getModel('Varien_Db_Select',
            Mage::getSingleton('core/resource')->getConnection('core_read')
        )->from($sales_flat_order_grid, array(
                    'customer_id as s_customer_id',
                    'sum(grand_total) as total_spend',
                    'count(*) as total_orders',
                    'avg(grand_total) as average_order_value',
                )
            )->group('customer_id')
        ;
        $customerCollection->getSelect()->columns(array(
            'last_purchase' => new Zend_Db_Expr("(SELECT created_at
                FROM $sales_flat_order
                WHERE customer_id =e.entity_id
                ORDER BY created_at DESC
                LIMIT 1)"),
            'last_order_no' => new Zend_Db_Expr("(SELECT entity_id
                FROM $sales_flat_order
                WHERE customer_id =e.entity_id
                ORDER BY created_at DESC
                LIMIT 1)")
            ));

        $customerCollection->getSelect()
            ->joinLeft(array($alias => $subselect),
                "{$alias}.s_customer_id = e.entity_id");



        $time = microtime(true);
        // create a customer object for each item in our collection
        foreach ($customerCollection as $item) {

            $customers[] = new Dotdigitalgroup_Email_Model_Connector_Customer($item, $mappingHash);
        }

        unset($customerCollection);
        $end_time = microtime(true);
        $end = $end_time - $time;


        $helper->log($pageSize . ': page' . $pageNum . ' for website: ' . $websiteId, null, $this->_log_filename);
        $helper->log('created dotmailer customers from collection: ' . gmdate("H:i:s", $end), null, $this->_log_filename);

        return $customers;
    }

    protected function allowResourceFullExecution() {

        /* it may be needed to set maximum execution time of the script to longer,
         * like 60 minutes than usual */
        set_time_limit ( 7200 );

        /* and memory to 512 megabytes */
        ini_set ( 'memory_limit', '512M' );

        return $this;
    }
    function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    /**
     * Create an array of columns we have chosen to map in our System->Config
     * @param Mage_Core_Model_Website $website
     * @return array
     */
    public function getCsvHeaderArray(Mage_Core_Model_Website $website) {

        $result = array();

        $result[] = 'Email';

        foreach ($this->getMappingHash($website) as $header) {
            if ($header != "0") $result[] = $header;
        }
        $result[] = 'emailType';

        return $result;
    }

    /**
     * Gets the datafield mapping hash from the system config
     * @param Mage_Core_Model_Website $website
     * @return array
     */
    protected function getMappingHash(Mage_Core_Model_Website $website){


        $customer_fields = array(
            'customer_data/firstname',
            'customer_data/lastname',
            'customer_data/dob',
            'customer_data/gender',
            'customer_data/title',
            'customer_data/website_created_on',
            'customer_data/store_created_on',
            'customer_data/account_created',
            'customer_data/last_logged_in',
            'customer_data/customer_group',
            'customer_data/billing_address_1',
            'customer_data/billing_address_2',
            'customer_data/billing_city',
            'customer_data/billing_country',
            'customer_data/billing_postcode',
            'customer_data/billing_telephone',
            'customer_data/delivery_address_1',
            'customer_data/delivery_address_2',
            'customer_data/delivery_city',
            'customer_data/delivery_country',
            'customer_data/delivery_postcode',
            'customer_data/delivery_telephone',
            'customer_data/total_orders',
            'customer_data/average_order_value',
            'customer_data/total_spend',
            'customer_data/last_order',
            'customer_data/last_order_no',
            'customer_data/customer_id'
        );

        $result = array();

        foreach ($customer_fields as $field) {

            $result[] = $website->getConfig('connector_data_field_settings/' . $field);

        }
        return $result;
    }

    public function setWishlists($customer, $website)
    {
        $customerId = $customer->id;
        $wishList = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId);

        /** @var  $connectorWishlist */
        $connectorWishlist = new Dotdigitalgroup_Email_Model_Connector_Wishlist($customer);
        $connectorWishlist->setId($wishList->getId());

        $wishListItemCollection = $wishList->getItemCollection();

        if (count($wishListItemCollection)) {
            foreach ($wishListItemCollection as $item) {
                /* @var $product Mage_Catalog_Model_Product */
                $product = $item->getProduct();
                $connectorItem = new Dotdigitalgroup_Email_Model_Customer_Wishlist_Item($product);
                $connectorItem->setQty($item->getQty());
                $connectorItem->setPrice($product);
                $connectorWishlist->setItem($connectorItem);//store for wishlists
            }
            //set wishlists for later use
            $this->wishlists[$website->getId()][] = $connectorWishlist;
        }
    }

}