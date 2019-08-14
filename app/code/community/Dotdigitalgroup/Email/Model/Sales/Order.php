<?php

class Dotdigitalgroup_Email_Model_Sales_Order
{
	/**
	 * @var array
	 */
	protected $accounts = array();
	/**
	 * @var string
	 */
	private $_apiUsername;
	/**
	 * @var string
	 */
	private $_apiPassword;

    /**
     * initial sync the transactional data
     * @return array
     */
    public function sync()
    {
        $client = Mage::getModel('email_connector/apiconnector_client');
        // Initialise a return hash containing results of our sync attempt
        $this->_searchAccounts();
	    Mage::helper( 'connector' )->log( 'search for the accounts, transactional order' );
	    foreach ($this->accounts as $account) {
            $orders = $account->getOrders();
            if (count($orders)) {
                $client->setApiUsername($account->getApiUsername())
                    ->setApiPassword($account->getApiPassword());
                Mage::helper('connector')->log('--------- Order sync ---------- : ' . count($orders));
                $client->postContactsTransactionalDataImport($orders, 'Orders');
                Mage::helper('connector')->log('----------end order sync----------');
            }
            unset($this->accounts[$account->getApiUsername()]);
        }
        return $this;
    }

    /**
     * Search the configuration data per website
     */
    private function _searchAccounts()
    {
        $helper = Mage::helper('connector');
        foreach (Mage::app()->getWebsites(true) as $website) {
            if ($helper->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_ORDER_ENABLED, $website)) {
                $this->_apiUsername = $helper->getApiUsername($website);
                $this->_apiPassword = $helper->getApiPassword($website);

                // limit for orders included to sync
                $limit = Mage::helper('connector')->getWebsiteConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_TRANSACTIONAL_DATA_SYNC_LIMIT, $website);
                if (!isset($this->accounts[$this->_apiUsername])) {
                    $account = Mage::getModel('email_connector/connector_account')
                        ->setApiUsername($this->_apiUsername)
                        ->setApiPassword($this->_apiPassword);
                    $this->accounts[$this->_apiUsername] = $account;
                }
                $this->accounts[$this->_apiUsername]->setOrders($this->getConnectorOrders($website, $limit));
            }
        }
    }

    /**
     * get all order to import.
     * @param $website
     * @param int $limit
     * @return array
     */
    public function getConnectorOrders($website, $limit = 100)
    {
        $orders = $customers = array();
        $storeIds = $website->getStoreIds();
        $orderModel   = Mage::getModel('email_connector/order');
        if(empty($storeIds))
            return;

        $orderStatuses = Mage::helper('connector')->getConfigSelectedStatus($website);

        if($orderStatuses)
            $orderCollection = $orderModel->getOrdersToImport($storeIds, $limit, $orderStatuses);
        else
            return;

        foreach ($orderCollection as $order) {
            try {
                $salesOrder = Mage::getModel('sales/order')->load($order->getOrderId());
                $storeId = $order->getStoreId();
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                /**
                 * Add guest to contacts table.
                 */
                if ($salesOrder->getCustomerIsGuest()) {
                    $this->_createGuestContact($salesOrder->getCustomerEmail(), $websiteId, $storeId);
                }
                if ($salesOrder->getId()) {
                    $connectorOrder = Mage::getModel('email_connector/connector_order', $salesOrder);
                    $orders[] = $connectorOrder;
                }
                $order->setEmailImported(Dotdigitalgroup_Email_Model_Contact::EMAIL_CONTACT_IMPORTED)
                    ->save();
            }catch(Exception $e){
                Mage::logException($e);
            }
        }
        return $orders;
    }

	/**
	 * Create a guest contact.
	 * @param $email
	 * @param $websiteId
	 * @param $storeId
	 *
	 * @return bool
	 */
	private function _createGuestContact($email, $websiteId, $storeId){
        try{
            $client = Mage::helper('connector')->getWebsiteApiClient($websiteId);
            $contactApi = $client->getContactByEmail($email);
            if (isset($contactApi->message) && $contactApi->message == Dotdigitalgroup_Email_Model_Apiconnector_Client::REST_CONTACT_NOT_FOUND) {
                //create a new contact.
                $contactApi = $client->postContacts($email);
            } elseif (isset($contactApi->message)) {
                return false;
            }
            // Add guest to address book
            $response = $client->postAddressBookContacts(Mage::helper('connector')->getGuestAddressBook($websiteId), $contactApi);
            $contactModel = Mage::getModel('email_connector/contact')->loadByCustomerEmail($email, $websiteId);
            $contactModel->setIsGuest(1)
                ->setStoreId($storeId)
                ->setContactId($contactApi->id)
                ->setEmailImported(1);

            if (isset($response->message) && $response->message == 'Contact is suppressed. ERROR_CONTACT_SUPPRESSED')
                $contactModel->setSuppressed(1);

            $contactModel->save();
            Mage::helper('connector')->log('-- guest found : '  . $email . ' website : ' . $websiteId . ' ,store : ' . $storeId);
        }catch(Exception $e){
            Mage::logException($e);
        }

        return true;
    }
}