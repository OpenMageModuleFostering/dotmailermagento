<?php

class Dotdigitalgroup_Email_Model_Apiconnector_Contact
{
    private $_start;
    private $_countCustomers;
    private $_wishlists;

	/**
	 * Contact sync.
	 *
	 * @return array
	 */
	public function sync()
    {
        $result = array('error' => false, 'message' => "Done.");
        /** @var Dotdigitalgroup_Email_Helper_Data $helper */
        $helper = Mage::helper('connector');
        $helper->log('---------- Start customer sync ----------');
        $this->_start = microtime(true);
        //resourse allocation
        $helper->allowResourceFullExecution();
        foreach (Mage::app()->getWebsites(true) as $website) {
            $enabled = Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_ENABLED, $website);
            $sync = Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_CONTACT_ENABLED, $website);
            if ($enabled && $sync)
                $this->exportCustomersForWebsite($website);
        }
        $helper->log('Total time for sync : ' . gmdate("H:i:s", microtime(true) - $this->_start));

        return $result;
    }

    public function exportCustomersForWebsite(Mage_Core_Model_Website $website){
        $updated = 0;
        $customers = $headers = $allMappedHash = array();
        $helper = Mage::helper('connector');
        $pageSize = $helper->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_LIMIT, $website);
        //skip if the mapping field is missing
        if(!$helper->getCustomerAddressBook($website))
            return;
        //reset wishlists
        $this->_wishlists = array();
        $fileHelper = Mage::helper('connector/file');
        $contactModel = Mage::getModel('email_connector/contact');
        $client = Mage::helper('connector')->getWebsiteApiClient($website);
        $contacts = $contactModel->getContactsToImportForWebsite($website->getId(), $pageSize);

        // no contacts for this webiste
        if (!count($contacts))
            return;
        //create customer filename
        $customersFile = strtolower($website->getCode() . '_customers_' . date('d_m_Y_Hi') . '.csv');
        $helper->log('Customers file : ' . $customersFile);

        //get customer ids
        $customerIds = array();
        foreach ($contacts as $contact) {
            $customerIds[] = $contact->getCustomerId();
        }
        //customer collection
        $customerCollection = $this->getCollection($customerIds);

        /**
         * HEADERS.
         */
        $mappedHash = $fileHelper->getWebsiteCustomerMappingDatafields($website);
        $headers = $mappedHash;
        //custom customer attributes
        $customAttributes = $helper->getCustomAttributes($website);
        foreach ($customAttributes as $data) {
            $headers[] = $data['datafield'];
            $allMappedHash[$data['attribute']] = $data['datafield'];
        }
        $headers[] = 'Email';
        $headers[] = 'EmailType';
        $fileHelper->outputCSV($fileHelper->getFilePath($customersFile), $headers);
        /**
         * END HEADERS.
         */


        foreach ($customerCollection as $customer) {
            $contactModel = Mage::getModel('email_connector/contact')->loadByCustomerId($customer->getId());
            //skip contacts without customer id
            if(!$contactModel->getId())
                continue;
            /**
             * DATA.
             */
            $connectorCustomer =  Mage::getModel('email_connector/apiconnector_customer', $mappedHash);
            $connectorCustomer->setCustomerData($customer);
            //count number of customers
            $customers[] = $connectorCustomer;
            foreach ($customAttributes as $data) {
                $attribute = $data['attribute'];
                $value = $customer->getData($attribute);
                $connectorCustomer->setData($value);
            }
            //contact email and email type
            $connectorCustomer->setData($customer->getEmail());
            $connectorCustomer->setData('Html');
            // save csv file data for customers
            $fileHelper->outputCSV($fileHelper->getFilePath($customersFile), $connectorCustomer->toCSVArray());

            /**
             * END DATA.
             */

            //mark the contact as imported
            $contactModel->setEmailImported(Dotdigitalgroup_Email_Model_Contact::EMAIL_CONTACT_IMPORTED);
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if ($subscriber->isSubscribed()) {
                $contactModel->setIsSubscriber(1)
                    ->setSubscriberStatus($subscriber->getSubscriberStatus());
            }

            $contactModel->save();

            //Send wishlist as transactional data
            if ($helper->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_WISHLIST_ENABLED, $website)) {
                $this->_setCustomerWishList($customer, $website);
            }
            $updated++;
        }
        //send wishlist as transactional data
        if (isset($this->_wishlists[$website->getId()])) {
            //send wishlists as transactional data
            $websiteWishlists = $this->_wishlists[$website->getId()];
            //remove wishlists one by one
            foreach ($websiteWishlists as $wishlist) {
                $email = $wishlist->email;
                $client->deleteContactTransactionalData($email, 'Wishlist');
            }
            //import wishlists in bulk
            $client->postContactsTransactionalDataImport($websiteWishlists, 'Wishlist');
        }

        $helper->log('Website : ' . $website->getName() . ', customers = ' . count($customers));
        $helper->log('---------------------------- execution time :' . gmdate("H:i:s", microtime(true) - $this->_start));

        if (file_exists($fileHelper->getFilePath($customersFile))) {
            //import contacts
            if ($updated > 0)
                $client->postAddressBookContactsImport($customersFile,  $helper->getCustomerAddressBook($website));
            //archive file on success
            $fileHelper->archiveCSV($customersFile);
        }
        $this->_countCustomers += $updated;
        return;
    }

    /**
     * @param $customer
     * @param $website
     */
    private function _setCustomerWishList($customer, $website){
        $website = Mage::app()->getWebsite($website);
        $customerId = $customer->getId();
        $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId);
        /** @var  $connectorWishlist */
        $connectorWishlist = Mage::getModel('email_connector/customer_wishlist', $customer);
        $connectorWishlist->setId($wishlist->getId());
        $wishListItemCollection = $wishlist->getItemCollection();
        if (count($wishListItemCollection)) {
            foreach ($wishListItemCollection as $item) {
                /* @var $product Mage_Catalog_Model_Product */
                $product = $item->getProduct();
                $wishlistItem = Mage::getModel('email_connector/customer_wishlist_item', $product)
                    ->setQty($item->getQty())
                    ->setPrice($product);
                //store for wishlists
                $connectorWishlist->setItem($wishlistItem);
            }
            //set wishlists for later use
            $this->_wishlists[$website->getId()][] = $connectorWishlist;
        }
    }

	/**
	 * Sync a single contact.
	 *
	 * @param null $contactId
	 *
	 * @return mixed
	 * @throws Mage_Core_Exception
	 */
	public function syncContact($contactId = null)
    {
        if ($contactId)
            $contact = Mage::getModel('email_connector/contact')->load($contactId);
        else {
            $contact = Mage::registry('current_contact');
        }
        if (! $contact->getId()) {
            Mage::getSingleton('adminhtml/session')->addError('No contact found!');
            return false;
        }

        $websiteId = $contact->getWebsiteId();
        $website = Mage::app()->getWebsite($websiteId);
        $updated = 0;
        $customers = $headers = $allMappedHash = array();
        $helper = Mage::helper('connector');
        $helper->log('---------- Start single customer sync ----------');
        //skip if the mapping field is missing
        if(!$helper->getCustomerAddressBook($website))
            return false;
        //reset wishlists
        $this->_wishlists = array();
        $fileHelper = Mage::helper('connector/file');

        $customerId = $contact->getCustomerId();
        if (!$customerId) {
            Mage::getSingleton('adminhtml/session')->addError('Cannot manually sync guests!');
            return false;
        }
        $client = Mage::helper('connector')->getWebsiteApiClient($website);

        //create customer filename
        $customersFile = strtolower($website->getCode() . '_customers_' . date('d_m_Y_Hi') . '.csv');
        $helper->log('Customers file : ' . $customersFile);

        /**
         * HEADERS.
         */
        $mappedHash = $fileHelper->getWebsiteCustomerMappingDatafields($website);
        $headers = $mappedHash;
        //custom customer attributes
        $customAttributes = $helper->getCustomAttributes($website);
        foreach ($customAttributes as $data) {
            $headers[] = $data['datafield'];
            $allMappedHash[$data['attribute']] = $data['datafield'];
        }
        $headers[] = 'Email';
        $headers[] = 'EmailType';
        $fileHelper->outputCSV($fileHelper->getFilePath($customersFile), $headers);
        /**
         * END HEADERS.
         */
        $customerCollection = $this->getCollection(array($customerId));

        foreach ($customerCollection as $customer) {
            $contactModel = Mage::getModel('email_connector/contact')->loadByCustomerId($customer->getId());
            //skip contacts without customer id
            if (!$contactModel->getId())
                continue;
            /**
             * DATA.
             */
            $connectorCustomer =  Mage::getModel('email_connector/apiconnector_customer', $mappedHash);
            $connectorCustomer->setCustomerData($customer);
            //count number of customers
            $customers[] = $connectorCustomer;
            foreach ($customAttributes as $data) {
                $attribute = $data['attribute'];
                $value = $customer->getData($attribute);
                $connectorCustomer->setData($value);
            }
            //contact email and email type
            $connectorCustomer->setData($customer->getEmail());
            $connectorCustomer->setData('Html');
            // save csv file data for customers
            $fileHelper->outputCSV($fileHelper->getFilePath($customersFile), $connectorCustomer->toCSVArray());

            /**
             * END DATA.
             */

            //mark the contact as imported
            $contactModel->setEmailImported(Dotdigitalgroup_Email_Model_Contact::EMAIL_CONTACT_IMPORTED);
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
            if ($subscriber->isSubscribed()) {
                $contactModel->setIsSubscriber(1)
                    ->setSubscriberStatus($subscriber->getSubscriberStatus());
            }

            $contactModel->save();

            //Send wishlist as transactional data
            if ($helper->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_WISHLIST_ENABLED, $website)) {
                $this->_setCustomerWishList($customer, $website);
            }
            $updated++;
        }
        //send wishlist as transactional data
        if (isset($this->_wishlists[$website->getId()])) {
            //send wishlists as transactional data
            $websiteWishlists = $this->_wishlists[$website->getId()];
            //remove wishlists one by one
            foreach ($websiteWishlists as $wishlist) {
                $email = $wishlist->email;
                $client->deleteContactTransactionalData($email, 'Wishlist');
            }
            //import wishlists in bulk
            $client->postContactsTransactionalDataImport($websiteWishlists, 'Wishlist');
        }

        if (file_exists($fileHelper->getFilePath($customersFile))) {
            //import contacts
            if ($updated > 0)
                $client->postAddressBookContactsImport($customersFile,   $helper->getCustomerAddressBook($website));
            //archive file on success
            $fileHelper->archiveCSV($customersFile);
        }
        return $contact->getEmail();
    }


    /**
     * get customer collection
     * @param $customerIds
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     * @throws Mage_Core_Exception
     */
    public function getCollection($customerIds)
    {
        $customerCollection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('*')
            ->joinAttribute('billing_street',       'customer_address/street',      'default_billing', null, 'left')
            ->joinAttribute('billing_city',         'customer_address/city',        'default_billing', null, 'left')
            ->joinAttribute('billing_country_code', 'customer_address/country_id',  'default_billing', null, 'left')
            ->joinAttribute('billing_postcode',     'customer_address/postcode',    'default_billing', null, 'left')
            ->joinAttribute('billing_telephone',    'customer_address/telephone',   'default_billing', null, 'left')
            ->joinAttribute('billing_region',       'customer_address/region',      'default_billing', null, 'left')
            ->joinAttribute('shipping_street',      'customer_address/street',      'default_shipping', null, 'left')
            ->joinAttribute('shipping_city',        'customer_address/city',        'default_shipping', null, 'left')
            ->joinAttribute('shipping_country_code','customer_address/country_id',  'default_shipping', null, 'left')
            ->joinAttribute('shipping_postcode',    'customer_address/postcode',    'default_shipping', null, 'left')
            ->joinAttribute('shipping_telephone',   'customer_address/telephone',   'default_shipping', null, 'left')
            ->joinAttribute('shipping_region',      'customer_address/region',      'default_shipping', null, 'left')
            ->addAttributeToFilter('entity_id', array('in' => $customerIds));
        $customer_log = Mage::getSingleton('core/resource')->getTableName('log_customer');
        $sales_flat_order_grid = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');

        // get the last login date from the log_customer table
        $customerCollection->getSelect()->columns(
            array('last_logged_date' => new Zend_Db_Expr ("(SELECT login_at FROM  $customer_log WHERE customer_id =e.entity_id ORDER BY log_id DESC LIMIT 1)")));

        // customer order information
        $alias = 'subselect';
        $subselect = Mage::getModel('Varien_Db_Select', Mage::getSingleton('core/resource')->getConnection('core_read'))
            ->from($sales_flat_order_grid, array(
                    'customer_id as s_customer_id',
                    'sum(grand_total) as total_spend',
                    'count(*) as number_of_orders',
                    'avg(grand_total) as average_order_value',
                )
            )->group('customer_id')
        ;
        $customerCollection->getSelect()->columns(array(
                'last_order_date' => new Zend_Db_Expr("(SELECT created_at FROM $sales_flat_order_grid WHERE customer_id =e.entity_id ORDER BY created_at DESC LIMIT 1)"),
                'last_order_id' => new Zend_Db_Expr("(SELECT entity_id FROM $sales_flat_order_grid WHERE customer_id =e.entity_id ORDER BY created_at DESC LIMIT 1)")
            )
        );
        $customerCollection->getSelect()
            ->joinLeft(array($alias => $subselect), "{$alias}.s_customer_id = e.entity_id");

        return $customerCollection;
    }
}