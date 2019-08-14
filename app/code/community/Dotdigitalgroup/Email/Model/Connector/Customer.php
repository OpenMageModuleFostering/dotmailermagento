<?php

class Dotdigitalgroup_Email_Model_Connector_Customer{

    // Unique identifier for Dotmail: Email
    private $_email;

    // Customer Info Data
    private $_firstname;
    private $_lastname;
    private $_dob;
    private $_gender;
    private $_title;
    private $_website_name;
    private $_store_name;
    private $_created_at;
    private $_customer_group;

    // Customer Address Data
    private $_billing_address_1;
    private $_billing_address_2;
    private $_billing_city;
    private $_billing_country;
    private $_billing_postcode;
    private $_billing_telephone;
    private $_delivery_address_1;
    private $_delivery_address_2;
    private $_delivery_city;
    private $_delivery_country;
    private $_delivery_postcode;
    private $_delivery_telephone;
    // Customer Sales Data
    private $_number_of_orders;
    private $_average_order_value;
    private $_total_spend;
    private $_last_order_date;
    private $_last_order_id;

    private $_customer_id;

    private $_last_logged_date;

    public function getEmail()
    {
        return $this->_email;
    }

    private $_mapping_hash;


    // class constructor - takes
    public function __construct( $customer){

        $this->_mapping_hash        = Mage::helper('connector')->getMappingHash($customer->getWebsiteId());
        //  map each attribute from the $customer parameter (collection item) to the corresponding scoped variable in this class
        $this->_email                   = $customer->getEmail();
        $this->_firstname               = $customer->getFirstname();
        $this->_lastname                = $customer->getLastname();
        $this->_dob                     = $customer->getDob();
        $this->_gender                  = $this->getCustomerGender($customer->getGender());
        $this->_title                   = $customer->getPrefix();

        $this->_website_name            = $this->getWebsiteName($customer->getWebsiteId());
        $this->_store_name              = $this->getStoreName($customer->getStoreId());
        $this->_created_at              = $customer->getCreatedAt();
        $this->_last_logged_date        = $customer->getLastLoggedDate();
        $this->_customer_group          = $this->getCustomerGroup($customer->getGroupId());
        $this->_billing_address_1       = $this->getStreet($customer->getBillingStreet(), 1);
        $this->_billing_address_2       = $this->getStreet($customer->getBillingStreet(), 2);

        $this->_billing_city            = $customer->getBillingCity();
        $this->_billing_country         = $customer->getBillingCountryCode();
        $this->_billing_postcode        = $customer->getBillingPostcode();
        $this->_billing_telephone       = $customer->getBillingTelephone();

        $this->_delivery_address_1      = $this->getStreet($customer->getShippingStreet(), 1);
        $this->_delivery_address_2      = $this->getStreet($customer->getShippingStreet(), 2);

        $this->_delivery_city           = $customer->getShippingCity();
        $this->_delivery_country        = $customer->getShippingCountryCode();
        $this->_delivery_postcode       = $customer->getShippingPostcode();
        $this->_delivery_telephone      = $customer->getShippingTelephone();

        $this->_number_of_orders        = $customer->getNumberOfOrders();
        $this->_average_order_value     = $customer->getAverageOrderValue();
        $this->_total_spend             = $customer->getTotalSpend();

        $this->_last_order_date         = $customer->getLastOrderDate();
        $this->_last_order_id           = $customer->getLastOrderId();

        $this->_customer_id             = $customer->getId();
    }

    public function toCSVArray() {

        // no iterating, just create an array()

        $result = array();

        // Email is the only required field in the CSV upload
        $result[] = $this->_email;

        if ($this->_mapping_hash[0]!="0")  $result[]    =   $this->_title;
        if ($this->_mapping_hash[1]!="0")  $result[]    =   $this->_firstname;
        if ($this->_mapping_hash[2]!="0")  $result[]    =   $this->_lastname;
        if ($this->_mapping_hash[3]!="0")  $result[]    =   $this->_dob;
        if ($this->_mapping_hash[4]!="0")  $result[]    =   $this->_gender;

        if ($this->_mapping_hash[5]!="0")  $result[]    =   $this->_website_name;
        if ($this->_mapping_hash[6]!="0")  $result[]    =   $this->_store_name;
        if ($this->_mapping_hash[7]!="0")  $result[]    =   $this->_created_at;
        if ($this->_mapping_hash[8]!="0")  $result[]    =   $this->_last_logged_date;
        if ($this->_mapping_hash[9]!="0")  $result[]    =   $this->_customer_group;

        if ($this->_mapping_hash[10]!="0") $result[]    =   $this->_billing_address_1;
        if ($this->_mapping_hash[11]!="0") $result[]    =   $this->_billing_address_2;
        if ($this->_mapping_hash[12]!="0") $result[]    =   $this->_billing_city;
        if ($this->_mapping_hash[13]!="0") $result[]    =   $this->_billing_country;
        if ($this->_mapping_hash[14]!="0") $result[]    =   $this->_billing_postcode;
        if ($this->_mapping_hash[15]!="0") $result[]    =   $this->_billing_telephone;

        if ($this->_mapping_hash[16]!="0") $result[]    =   $this->_delivery_address_1;
        if ($this->_mapping_hash[17]!="0") $result[]    =   $this->_delivery_address_2;
        if ($this->_mapping_hash[18]!="0") $result[]    =   $this->_delivery_city;
        if ($this->_mapping_hash[19]!="0") $result[]    =   $this->_delivery_country;
        if ($this->_mapping_hash[20]!="0") $result[]    =   $this->_delivery_postcode;
        if ($this->_mapping_hash[21]!="0") $result[]    =   $this->_delivery_telephone;

        if ($this->_mapping_hash[22]!="0") $result[]    =   $this->_number_of_orders;
        if ($this->_mapping_hash[23]!="0") $result[]    =   $this->_average_order_value;
        if ($this->_mapping_hash[24]!="0") $result[]    =   $this->_total_spend;
        if ($this->_mapping_hash[25]!="0") $result[]    =   $this->_last_order_date;
        if ($this->_mapping_hash[26]!="0") $result[]    =   $this->_last_order_id;
        if ($this->_mapping_hash[27]!="0") $result[]    =   $this->_customer_id;

        $result[] = 'Html';

        return $result;

    }

    // returns the object as JSON
    public function toJSON(){

        return json_encode($this->expose());

    }

    // exposes the class as an array of objects
    public function expose() {

        return get_object_vars($this);

    }

    /**
     * return the  gender text label
     * @param $genderId customer option id
     * @return string
     */
    public function getCustomerGender($genderId)
    {
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

    /**
     * get the street name by line number
     * @param $street
     * @param $line
     * @return string
     */
    public function getStreet($street, $line)
    {
        $street = explode("\n", $street);
        if($line == 1){
            return $street[0];
        }
        if(isset($street[$line -1])){

            return $street[$line - 1];
        }else{

            return '';
        }
    }

    /**
     * get website name
     * @param $websiteId
     * @return null|string
     */
    public function getWebsiteName($websiteId)
    {
        $website = Mage::app()->getWebsite($websiteId);
        if($website)
            return $website->getName();

        return '';
    }

    public function getStoreName($storeId)
    {
        $store = Mage::app()->getStore($storeId);
        if($store)
            return $store->getName();

        return '';
    }

    /**
     * get the group name
     * @param $groupId
     * @return string
     */
    public function getCustomerGroup($groupId)
    {
        $group = Mage::getModel('customer/group')->load($groupId);
        if($group){
            return $group->getCode();
        }
        return '';
    }

}
