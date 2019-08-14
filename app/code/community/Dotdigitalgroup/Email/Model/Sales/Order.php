<?php

class Dotdigitalgroup_Email_Model_Sales_Order
{
    const XML_PATH_TRANSACTIONAL_DATA_SYNC_LIMIT          = 'connector_advanced_settings/sync_limits/orders';

    protected $accounts = array();
    private $_api_username;
    private $_api_password;

    /**
     * initial sync the transactional data
     * @return array
     */
    public function sync()
    {
        Mage::helper('connector')->log('start order sync..');
        $client = Mage::getModel('connector/connector_api_client');
        // Initialise a return hash containing results of our sync attempt
        $this->_searchAccounts();

        foreach($this->accounts as $account){
            if(count($account->getOrders())){
                $client->postContactsTransactionalDataImport($account->getOrders(), 'Orders');
            }

            unset($this->accounts[$account->getApiUsername()]);
        }
        Mage::helper('connector')->log('end order sync.');
        return $this;
    }

    /**
     *Search the configuration data per website
     */
    private function _searchAccounts()
    {
        $helper = Mage::helper('connector');
        foreach (Mage::app()->getWebsites() as $website){

            $this->_api_username = $helper->getApiUsername($website);
            $this->_api_password = $helper->getApiPassword($website);

            // limit for orders included to sync
            $limit = Mage::helper('connector')->getTransactionalSyncLimit();

            if(!isset($this->accounts[$this->_api_username])){
                $account = Mage::getModel('connector/connector_account')
                    ->setApiUsername($this->_api_username)
                    ->setApiPassword($this->_api_password);

                $this->accounts[$this->_api_username] = $account;
            }
            $this->accounts[$this->_api_username]->setOrders($this->getConnectorOrders($limit));

        }
    }

    /**
     * get all order to import
     * @param $limit
     * @return array
     */
    public function getConnectorOrders($limit = 100)
    {
        $orders = $customers = array();
        $orderModel   = Mage::getModel('connector/email_order');
        $orderCollection = $orderModel->getOrdersToImport($limit);


        foreach ($orderCollection as $order) {
            try {
                $salesOrder = Mage::getModel('sales/order')->load($order->getOrderId());

                $websiteId  = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();

                /**
                 * Get guest and add to contacts
                 */
                if($salesOrder->getCustomerIsGuest()){

                    $this->_createGuestContact($salesOrder->getCustomerEmail(), $websiteId);
                }

                //@todo report the deleted orders in log table
                if($salesOrder->getId()){
                    $connectorOrder = Mage::getModel('connector/connector_order', $salesOrder);
                    $connectorOrder->connector_id = $salesOrder->getCustomerEmail();
                    $orders[] = $connectorOrder;
                }
                //mark order as imported
                $order->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_IMPORTED)->save();


            }catch(Exception $e){
                Mage::helper('connector')->log($e->getMessage());
            }
        }
        return $orders;
    }
    private function _createGuestContact($email, $websiteId){
        try{
            $client = Mage::getModel('connector/connector_api_client');
            $client->setApiUsername(Mage::helper('connector')->getApiUsername($websiteId))
                ->setApiPassword(Mage::helper('connector')->getApiPassword($websiteId));

            $contactApi = $client->getContactByEmail($email);
            if(isset($contactApi->message) && $contactApi->message == Dotdigitalgroup_Email_Model_Connector_Api_Client::REST_CONTACT_NOT_FOUND){
                $contactApi = $client->postContacts($email);


            }elseif(isset($contactApi->message)){
                return false;
            }
            // Add guest to address book
            $client->postAddressBookContacts(Mage::helper('connector')->getGuestAddressBook($websiteId), $contactApi);

            /**
             * Create new contact
             */
            $contactModel = Mage::getModel('connector/email_contact')->loadByCustomerEmail($email, $websiteId);

            $contactModel->setIsGuest(1)
                ->setContactId($contactApi->id)
                ->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_NOT_IMPORTED)
                ->save();


            Mage::helper('connector')->log('found guest : '  . $email . ' website ' . $websiteId);
        }catch(Exception $e){
            Mage::helper('connector')->log($e->getMessage());
        }

        return true;

    }
}