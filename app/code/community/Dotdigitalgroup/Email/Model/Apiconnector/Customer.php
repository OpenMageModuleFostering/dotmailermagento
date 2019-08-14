<?php

class Dotdigitalgroup_Email_Model_Apiconnector_Customer
{

    public $customer;
    public $customerData ;

    protected $_mapping_hash;


    // class constructor - takes
    public function __construct( $mappingHash){
        $this->setMappigHash($mappingHash);
    }

    public function setData($data)
    {
        $this->customerData[] = $data;
    }

    public function setCustomerData(Mage_Customer_Model_Customer $customer)
    {
        $this->customer = $customer;

        foreach ($this->getMappingHash() as $key => $field) {

            /**
             * call user function based on the attribute mapped.
             */
            $function = 'get';
            $exploded = explode('_', $key);
            foreach ($exploded as $one) {
                $function .= ucfirst($one);
            }
            try{
                $value = call_user_func(array('self', $function));
                $this->customerData[$key] = $value;
            }catch (Exception $e){
                Mage::logException($e);
            }
        }
    }

    public function getCustomerId()
    {
        return $this->customer->getId();
    }

    public function getFirstname(){
        return $this->customer->getFirstname();
    }

    public function getLastname()
    {
        return $this->customer->getLastname();
    }

    public function getDob()
    {
        return $this->customer->getDob();
    }
    public function getGender(){
        return $this->_getCustomerGender();
    }

    public function getPrefix()
    {
        return $this->customer->getPrefix();
    }

    public function getSuffix()
    {
        return $this->customer->getSuffix();
    }

    public function getWebsiteName()
    {
        return $this->_getWebsiteName();
    }

    public function getStoreName()
    {
        return $this->_getStoreName();
    }

    public function getCreatedAt()
    {
        return $this->customer->getCreatedAt();
    }

    public function getLastLoggedDate()
    {
        return $this->customer->getLastLoggedDate();
    }

    public function getCustomerGroup()
    {
        return $this->_getCustomerGroup();
    }

    public function getBillingAddress1()
    {
        return $this->_getStreet($this->customer->getBillingStreet(), 1);
    }

    public function getBillingAddress2()
    {
        return $this->_getStreet($this->customer->getBillingStreet(), 2);
    }

    public function getBillingCity()
    {
        return $this->customer->getBillingCity();
    }

    public function getBillingCountry()
    {
        return $this->customer->getBillingCountryCode();
    }

    public function getBillingState()
    {
        return $this->customer->getBillingRegion();
    }

    public function getBillingPostcode()
    {
        return $this->customer->getBillingPostcode();
    }

    public function getBillingTelephone()
    {
        return $this->customer->getBillingTelephone();
    }

    public function getDeliveryAddress1()
    {
        return $this->_getStreet($this->customer->getShippingStreet(), 1);
    }

    public function getDeliveryAddress2()
    {
        return $this->_getStreet($this->customer->getShippingStreet(), 2);
    }

    public function getDeliveryCity()
    {
        return $this->customer->getShippingCity();
    }

    public function getDeliveryCountry(){
        return $this->customer->getShippingCountryCode();
    }

    public function getDeliveryState()
    {
        return $this->customer->getShippingRegion();
    }

    public function getDeliveryPostcode()
    {
        return $this->customer->getShippingPostcode();
    }

    public function getDeliveryTelephone(){
        return $this->customer->getShippingTelephone();
    }

    public function getNumberOfOrders()
    {
        return $this->customer->getNumberOfOrders();
    }

    public function getAverageOrderValue()
    {
        return $this->customer->getAverageOrderValue();
    }

    public function getTotalSpend()
    {
        return $this->customer->getTotalSpend();
    }

    public function getLastOrderDate()
    {
        return $this->customer->getLastOrderDate();
    }

    public function getLastOrderId()
    {
        return $this->customer->getLastOrderId();
    }
    public function getId()
    {
        return $this->customer->getId();
    }

    public function getTitle()
    {
        return $this->customer->getPrefix();
    }

    public function getTotalRefund()
    {
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('customer_id', $this->customer->getId())
        ;
        $totalRefunded = 0;
        foreach ($orders as $order) {
            $refunded = $order->getTotalRefunded();
            $totalRefunded += $refunded;
        }

        return $totalRefunded;
    }

    public function toCSVArray()
    {
        $result = $this->customerData;
        return $result;
    }

    private function _getCustomerGender(){
        $genderId = $this->customer->getGender();
        if(is_numeric($genderId)){
            $gender = Mage::getResourceModel('customer/customer')
                ->getAttribute('gender')
                ->getSource()
                ->getOptionText($genderId)
            ;
            return $gender;
        }

        return '';
    }

    private function _getStreet($street, $line){
        $street = explode("\n", $street);
        if(isset($street[$line - 1]))
            return $street[$line - 1];
        return '';
    }

    private function _getWebsiteName(){
        $websiteId = $this->customer->getWebsiteId();
        $website = Mage::app()->getWebsite($websiteId);
        if($website)
            return $website->getName();

        return '';
    }

    private  function _getStoreName()
    {
        $storeId = $this->customer->getStoreId();
        $store = Mage::app()->getStore($storeId);
        if($store)
            return $store->getName();

        return '';
    }

    /**
     * @param mixed $mapping_hash
     */
    public function setMappingHash($mapping_hash)
    {
        $this->_mapping_hash = $mapping_hash;
    }

    /**
     * @return mixed
     */
    public function getMappingHash()
    {
        return $this->_mapping_hash;
    }

    private function _getCustomerGroup(){
        $groupId = $this->customer->getGroupId();
        $group = Mage::getModel('customer/group')->load($groupId);
        if($group){
            return $group->getCode();
        }
        return '';
    }

    public function setMappigHash( $value)
    {
        $this->_mapping_hash = $value;
        return $this;
    }

}