<?php

class Dotdigitalgroup_Email_Model_Customer_Contact
{
    private $_start;
    private $_countCustomers;
    protected $wishlists;

    public function sync()
    {
        /** @var Dotdigitalgroup_Email_Helper_Data $helper */
        $helper = Mage::helper('connector');
        $helper->log('Start customer sync..');
        $this->_start = microtime(true);

        //resourse allocation
        $helper->allowResourceFullExecution();

        $result = array('error' => false, 'message' => "Done.");

        foreach(Mage::app()->getWebsites(true) as $website){

            $this->_exportCustomersForWebsite($website);
        }

        $helper->log('Total time for sync : ' . gmdate("H:i:s", microtime(true) - $this->_start));
        return $result;
    }



    private function _exportCustomersForWebsite(Mage_Core_Model_Website $website){

        $helper = Mage::helper('connector');
        $updated = 0;
        $customers = array();
        //skip if the mapping field is missing
        if(!$helper->getCustomerAddressBook($website))
            return;
        //reset wishlists
        $this->wishlists = array();
        $fileHelper = Mage::helper('connector/file');
        $contactModel = Mage::getModel('connector/email_contact');
        $client = Mage::getModel('connector/connector_api_client');
        $client->setApiUsername($helper->getApiUsername($website));
        $client->setApiPassword($helper->getApiPassword($website));

        // Contacts to import for website
        $pageSize = $helper->getSyncLimit();
        $contacts = $contactModel->getContactsToImportForWebsite($website->getId(), $pageSize);

        // no contacts for this webiste
        if(!count($contacts))
            return;

        //create customer filename
        $customersFile       = strtolower($website->getCode() . '_customers_' . date('d_m_Y_Hi') . '.csv');
        $helper->log('Customers file : ' . $customersFile);

        //get customer ids
        $customerIds = array();
        foreach($contacts as $contact){
            $customerIds[] = $contact->getCustomerId();
        }

        //customer collection
        $customerCollection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('dob')
            ->addAttributeToSelect('gender')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('last_logged_in')
            ->addAttributeToSelect('group_id')
            ->addAttributeToSelect('dob')
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
            ->addAttributeToFilter('entity_id', array('in' => $customerIds))
        ;

        $customer_log = Mage::getSingleton('core/resource')->getTableName('log_customer');
        $sales_flat_order_grid = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');

        // get the last login date from the log_customer table
        $customerCollection->getSelect()->columns(
            array('last_logged_date' => new Zend_Db_Expr ("(SELECT login_at
                    FROM  $customer_log WHERE customer_id =e.entity_id ORDER BY log_id DESC LIMIT 1)")));

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
                'last_order_date' => new Zend_Db_Expr("(SELECT created_at
                FROM $sales_flat_order_grid
                WHERE customer_id =e.entity_id
                ORDER BY created_at DESC
                LIMIT 1)"),
                'last_order_id' => new Zend_Db_Expr("(SELECT entity_id
                FROM $sales_flat_order_grid
                WHERE customer_id =e.entity_id
                ORDER BY created_at DESC
                LIMIT 1)")
            )
        );
        $customerCollection->getSelect()
            ->joinLeft(array($alias => $subselect),
                "{$alias}.s_customer_id = e.entity_id");

        //write the csv headers
        $fileHelper->outputCSV($fileHelper->getFilePath($customersFile), $fileHelper->getCsvHeaderArray($website));

        foreach($customerCollection as $customer){
            $connectorCustomer =  Mage::getModel('connector/connector_customer', $customer);
            $contactModel = Mage::getModel('connector/email_contact')->loadByCustomerId($customer->getId());

            //skip contacts without customer id
            if(!$contactModel->getId())
                continue;
            $customers[] = $connectorCustomer;

            //mark the contact as imported
            $contactModel->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_IMPORTED)->save();

            //Send wishlist as transactional data
            if($helper->getWishlistEnabled() && $contactId = $contactModel->getContactId()){
                $this->setCustomerWishList($customer, $contactId, $website);
            }

            // save csv file data for customers
            $fileHelper->outputCSV($fileHelper->getFilePath($customersFile), $connectorCustomer->toCSVArray());
            $updated++;
        }
        //send wishlist as transactional data
        if(isset($this->wishlists[$website->getId()])){
            //send wishlists as transactional data
            $wishlists = $this->wishlists[$website->getId()];
            $client->deleteContactTransactionalData($contactId, 'Wishlist');
            $client->postContactsTransactionalDataImport($wishlists, $collectionName = 'Wishlist');
        }

        $helper->log('Website : ' . $website->getName() . ', customers = ' . count($customers));
        $helper->log('-----------------------------------------------execution time :' . gmdate("H:i:s", microtime(true) - $this->_start));

        if(file_exists($fileHelper->getFilePath($customersFile))){
            //import contacts
            if($updated > 0)
                $client->postAddressBookContactsImport($customersFile,   $helper->getCustomerAddressBook($website));
            //archive file on success
            $fileHelper->archiveCSV($customersFile);
        }
        $this->_countCustomers = $updated;
        return;
    }

    /**
     * @param $customer
     * @param $contactId
     * @param $website
     */
    public function setCustomerWishList($customer, $contactId, $website)
    {
        $website = Mage::app()->getWebsite($website);
        $customerId = $customer->getId();
        $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId);

        /** @var  $connectorWishlist */
        $connectorWishlist = Mage::getModel('connector/customer_wishlist', $customer);
        $connectorWishlist->setId($wishlist->getId())
            ->setConnectorId($contactId);
        $wishListItemCollection = $wishlist->getItemCollection();

        if (count($wishListItemCollection)) {
            foreach ($wishListItemCollection as $item) {
                /* @var $product Mage_Catalog_Model_Product */
                $product = $item->getProduct();
                $wishlistItem = Mage::getModel('connector/customer_wishlist_item', $product)
                    ->setQty($item->getQty());
                $wishlistItem->setPrice($product);

                $connectorWishlist->setItem($wishlistItem);//store for wishlists
            }
            //set wishlists for later use
            $this->wishlists[$website->getId()][] = $connectorWishlist;
        }
    }
}