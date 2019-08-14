<?php

class Dotdigitalgroup_Email_Model_Sales_Quote
{
    //customer
    const XML_PATH_LOSTBASKET_CUSTOMER_ENABLED_1        = 'connector_lost_baskets/customers/enabled_1';
    const XML_PATH_LOSTBASKET_CUSTOMER_ENABLED_2        = 'connector_lost_baskets/customers/enabled_2';
    const XML_PATH_LOSTBASKET_CUSTOMER_ENABLED_3        = 'connector_lost_baskets/customers/enabled_3';
    const XML_PATH_LOSTBASKET_CUSTOMER_INTERVAL_1       = 'connector_lost_baskets/customers/send_after_1';
    const XML_PATH_LOSTBASKET_CUSTOMER_INTERVAL_2       = 'connector_lost_baskets/customers/send_after_2';
    const XML_PATH_LOSTBASKET_CUSTOMER_INTERVAL_3       = 'connector_lost_baskets/customers/send_after_3';
    const XML_PATH_LOSTBASKET_CUSTOMER_CAMPAIGN_1       = 'connector_lost_baskets/customers/campaign_1';
    const XML_PATH_LOSTBASKET_CUSTOMER_CAMPAIGN_2       = 'connector_lost_baskets/customers/campaign_2';
    const XML_PATH_LOSTBASKET_CUSTOMER_CAMPAIGN_3       = 'connector_lost_baskets/customers/campaign_3';

    //guest
    const XML_PATH_LOSTBASKET_GUEST_ENABLED_1           = 'connector_lost_baskets/guests/enabled_1';
    const XML_PATH_LOSTBASKET_GUEST_ENABLED_2           = 'connector_lost_baskets/guests/enabled_2';
    const XML_PATH_LOSTBASKET_GUEST_ENABLED_3           = 'connector_lost_baskets/guests/enabled_3';
    const XML_PATH_LOSTBASKET_GUEST_INTERVAL_1          = 'connector_lost_baskets/guests/send_after_1';
    const XML_PATH_LOSTBASKET_GUEST_INTERVAL_2          = 'connector_lost_baskets/guests/send_after_2';
    const XML_PATH_LOSTBASKET_GUEST_INTERVAL_3          = 'connector_lost_baskets/guests/send_after_3';
    const XML_PATH_LOSTBASKET_GUEST_CAMPAIGN_1          = 'connector_lost_baskets/guests/campaign_1';
    const XML_PATH_LOSTBASKET_GUEST_CAMPAIGN_2          = 'connector_lost_baskets/guests/campaign_2';
    const XML_PATH_LOSTBASKET_GUEST_CAMPAIGN_3          = 'connector_lost_baskets/guests/campaign_3';


    public $lostBasketCustomers = array(1, 2, 3);
    public $lostBasketGuests = array(1, 2, 3);


    public function proccessLostBaskets()
    {
        /**
         * Save lost baskets to be send in Send table.
         */
        foreach (Mage::app()->getStores() as $store){
            $storeId = $store->getId();
            $sendModel = Mage::getModel('email_connector/campaign');
            /**
             * Customers campaings
             */
            foreach ($this->lostBasketCustomers as $num) {
                if($this->_getLostBasketCustomerEnabled($num, $storeId)){

                    if($num == 1)
                        $from = Zend_Date::now()->subMinute($this->_getLostBasketCustomerInterval($num, $storeId));
                    else
                        $from = Zend_Date::now()->subHour($this->_getLostBasketCustomerInterval($num, $storeId));
                    $to = clone($from);
                    $from->sub('5', Zend_Date::MINUTE);
                    $quoteCollection = $this->_getStoreQuotes($from->toString('YYYY-MM-dd HH:mm'), $to->toString('YYYY-MM-dd HH:mm'), $guest = false, $storeId);
                    if($quoteCollection->getSize())
                        Mage::helper('connector')->log('Customer lost baskets : ' . $num  . ', from : ' . $from->toString('YYYY-MM-dd HH:mm') . ':' . $to->toString('YYYY-MM-dd HH:mm'));
                    $campaignId = $this->_getLostBasketCustomerCampaignId($num, $storeId);
                    foreach ($quoteCollection as $quote) {
                        //save lost basket for sending
                        $sendModel->loadByQuoteId($quote->getId(), $storeId)
                            ->setEmail($quote->getCustomerEmail())
                            ->setCustomerId($quote->getCustomerId())
                            ->setEventName('Lost Basket')
                            ->setCampaignId($campaignId)
                            ->setStoreId($storeId)
                            ->setIsSent(null)->save();
                    }
                }

            }
            /**
             * Guests campaigns
             */
            foreach ($this->lostBasketGuests as $num) {
                if($this->_getLostBasketGuestEnabled($num, $storeId)){
                    if($num == 1)
                        $from = Zend_Date::now()->subMinute($this->_getLostBasketGuestIterval($num, $storeId));
                    else
                        $from = Zend_Date::now()->subHour($this->_getLostBasketGuestIterval($num, $storeId));
                    $to = clone($from);
                    $from->sub('5', Zend_Date::MINUTE);
                    $quoteCollection = $this->_getStoreQuotes($from->toString('YYYY-MM-dd HH:mm'), $to->toString('YYYY-MM-dd HH:mm'), $guest = true, $storeId);
                    if($quoteCollection->getSize())
                        Mage::helper('connector')->log('Guest lost baskets : ' . $num  . ', from : ' . $from->toString('YYYY-MM-dd HH:mm') . ':' . $to->toString('YYYY-MM-dd HH:mm'));
                    $guestCampaignId = $this->_getLostBasketGuestCampaignId($num, $storeId);
                    foreach ($quoteCollection as $quote) {
                        //save lost basket for sending
                        $sendModel->loadByQuoteId($quote->getId(), $storeId)
                            ->setEmail($quote->getCustomerEmail())
                            ->setEventName('Lost Basket')
                            ->setCheckoutMethod('Guest')
                            ->setCampaignId($guestCampaignId)
                            ->setStoreId($storeId)
                            ->setIsSent(null)->save();
                    }
                }
            }

        }
    }

    private function _getLostBasketCustomerCampaignId($num, $storeId){
        $store = Mage::app()->getStore($storeId);
        return $store->getConfig(constant('self::XML_PATH_LOSTBASKET_CUSTOMER_CAMPAIGN_' . $num));
    }
    private function _getLostBasketGuestCampaignId($num, $storeId){
        $store = Mage::app()->getStore($storeId);
        return $store->getConfig(constant('self::XML_PATH_LOSTBASKET_GUEST_CAMPAIGN_'. $num));
    }

    private function _getLostBasketCustomerInterval($num, $storeId){

        $store = Mage::app()->getstore($storeId);
        return $store->getConfig(constant('self::XML_PATH_LOSTBASKET_CUSTOMER_INTERVAL_' . $num));
    }

    private function _getLostBasketGuestIterval($num, $storeId){
        $store = Mage::app()->getStore($storeId);
        return $store->getConfig(constant('self::XML_PATH_LOSTBASKET_GUEST_INTERVAL_' . $num));
    }


    public function _getLostBasketCustomerEnabled($num, $storeId)
    {
        $store = Mage::app()->getStore($storeId);
        $enabled = $store->getConfig(constant('self::XML_PATH_LOSTBASKET_CUSTOMER_ENABLED_' . $num));
        return $enabled;

    }

    public function _getLostBasketGuestEnabled($num, $storeId)
    {
        $store = Mage::app()->getStore($storeId);
        return $store->getConfig(constant('self::XML_PATH_LOSTBASKET_GUEST_ENABLED_' . $num));
    }


    /**
     * @param string $from
     * @param string $to
     * @param bool $guest
     * @param int $storeId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    private function _getStoreQuotes($from = null, $to = null, $guest = false, $storeId = 0){

        $updated = array(
            'from' => $from,
            'to' => $to,
            'date' => true);

        $salesCollection = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('items_count', array('gt' => 0))
            ->addFieldToFilter('customer_email', array('neq' => ''))
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('updated_at', $updated);
        if($guest)
            $salesCollection->addFieldToFilter('checkout_method' , Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
        return $salesCollection;
    }
}