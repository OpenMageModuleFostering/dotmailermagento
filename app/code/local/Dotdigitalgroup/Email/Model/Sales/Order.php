<?php

class Dotdigitalgroup_Email_Model_Sales_Order extends Dotdigitalgroup_Email_Model_Api_Rest
{
    const XML_PATH_TRANSACTIONAL_DATA_ENABLED             = 'connector_sync_settings/transactional_data/order_enabled';
    const XML_PATH_TRANSACTIONAL_DATA_SYNC_LIMIT          = 'connector_advanced_settings/sync_limits/orders';

    protected $accounts = array();

    /**
     * initial sync the transactional data
     * @return array
     */
    public function sync()
    {
        // Initialise a return hash containing results of our sync attempt
        $this->_searchAccounts();

        foreach ($this->accounts as $account){
            if(count($account->getOrders())){
                $this->sendMultiTransactionalData($account->getOrders(), 'Order');

            }

            unset($this->accounts[$account->getApiUsername()]);
        }
        return $this;
    }

    /**
     *Search the configuration data per website
     */
    private function _searchAccounts()
    {
        foreach (Mage::app()->getWebsites() as $website){
            $enabled = $website->getConfig(self::XML_PATH_TRANSACTIONAL_DATA_ENABLED);

            if(!$enabled)
                continue;

            $this->_api_user = $website->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME);
            $limit = $website->getConfig(self::XML_PATH_TRANSACTIONAL_DATA_SYNC_LIMIT);

            if(!isset($this->accounts[$this->_api_user])){
                $account = new Dotdigitalgroup_Email_Model_Connector_Account();
                $account->setApiUsername($this->_api_user);
                $account->setApiPassword($this->_api_password);
                $this->accounts[$this->_api_user] = $account;
            }
            $this->accounts[$this->_api_user]->setOrders($this->getConnectorOrders($limit));
        }
    }

    /**
     * get all connector orders data
     * @param $limit
     * @return array
     */
    public function getConnectorOrders($limit = 100)
    {
        $orders = $customers = array();
        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('dotmailer_order_imported', array('null' => true), 'left')
            ->setPageSize($limit);

        $this->_helper->log('GET CONNECTOR ORDERS ' . $orderCollection->count() . ' limit ' . $limit, null, $this->_log_filename);

        //mark as imported for customers with contact id
        foreach ($orderCollection as $order) {
            try {
                $customerEmail = $order->getCustomerEmail();
                $storeId = $order->getStoreId();
                $this->setStoreId(Mage::app()->getStore(true)->getId());
                $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();

                $customerModel = Mage::getModel('customer/customer')->setWebsiteId($websiteId)
                    ->loadByEmail($customerEmail);

                if($customerModel){
                    $contactId = $customerModel->getData('dotmailer_contact_id');
                    if($contactId){
                        $orders[] = new Dotdigitalgroup_Email_Model_Connector_Order($order);
                        $order->setData('dotmailer_order_imported', 1);
                        $order->save();
                    }
                }
            }catch(Exception $e){


                $this->_helper->log($order->getIncrementId(), null, $this->_log_filename);
                $this->_helper->log($e->getMessage(), null, $this->_log_filename);
            }
        }
        return $orders;
    }
}