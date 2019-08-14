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
    private $_website_created_on;
    private $_store_created_on;
    private $_account_created;
    private $_last_logged_in;
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
    private $_total_orders;
    private $_average_order_value;
    private $_total_spend;
    private $_last_order;
    private $_last_order_no;
    // Newletter subscriber?
    private $_is_subscribed_to_newsletter;
    private $_dotmailer_contact_id;
    private $_customer_id;

    /**
    'firstname',
    'lastname',
    'dob',
    'gender',
    'title',
    'website_created_on',
    'store_created_on',
    'account_created',
    'last_logged_in',
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
    'total_orders',
    'average_order_value',
    'total_spend',
    'last_order',
    'last_order_no'
     * 
     */

    public function getEmail()
    {
        return $this->_email;
    }


    public function setDotmailerContactId($dotmailer_contact_id)
    {
        $this->_dotmailer_contact_id = $dotmailer_contact_id;
    }

    public function getDotmailerContactId()
    {
        return $this->_dotmailer_contact_id;
    }

    public function isSubscribed()
    {
        return $this->_is_subscribed_to_newsletter;
    }

    private $_mapping_hash;


    // class constructor - takes
    public function __construct(Mage_Customer_Model_Customer $customer, $mapping_hash){

        $this->_mapping_hash        = $mapping_hash;

        //  map each attribute from the $customer parameter (collection item) to the corresponding scoped variable in this class
        $this->id                   = $customer->getId();
        $this->_email               = $customer->getEmail();
        $this->_firstname           = $customer->getFirstname();
        $this->_lastname            = $customer->getLastname();
        $this->_dob                 = $customer->getDob();
        $this->_gender              = $this->getCustomerGender($customer->getGender());
        $this->_title               = $customer->getPrefix();

        $this->_website_created_on  = $this->getWebsiteName($customer->getWebsiteId());
        $this->_store_created_on    = $this->getStoreName($customer->getStoreId());
        $this->_account_created     = $customer->getCreatedAt();
        $this->_last_logged_in      = $customer->getLastLoggedIn();
        $this->_customer_group      = $this->getCustomerGroup($customer->getGroupId());
        $this->_billing_address_1   = $this->getStreet($customer->getBillingStreet(), 1);
        $this->_billing_address_2   = $this->getStreet($customer->getBillingStreet(), 2);

        $this->_billing_city        = $customer->getBillingCity();
        $this->_billing_country     = $customer->getBillingCountryCode();
        $this->_billing_postcode    = $customer->getBillingPostcode();
        $this->_billing_telephone   = $customer->getBillingTelephone();

        $this->_delivery_address_1  = $this->getStreet($customer->getShippingStreet(), 1);
        $this->_delivery_address_2  = $this->getStreet($customer->getShippingStreet(), 2);

        $this->_delivery_city       = $customer->getShippingCity();
        $this->_delivery_country    = $customer->getShippingCountryCode();
        $this->_delivery_postcode   = $customer->getShippingPostcode();
        $this->_delivery_telephone  = $customer->getShippingTelephone();

        $this->_total_orders        = $customer->getTotalOrders();
        $this->_average_order_value = $customer->getAverageOrderValue();
        $this->_total_spend         = $customer->getTotalSpend();

        $lastOrder = new Zend_Date($customer->getLastPurchase());
        //$this->_last_order          = $lastOrder->toString(Zend_Date::ISO_8601);
        $this->_last_order          = $customer->getLastPurchase();
        $this->_last_order_no       = $customer->getLastOrderNo();

        $this->_is_subscribed_to_newsletter = ($customer->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)? true : false;
        $this->_dotmailer_contact_id = $customer->getDotmailerContactId();
        $this->_customer_id          = $customer->getId();
    }

    public function toCSVArray() {

        // no iterating, just create an array()

        $result = array();

        // Email is the only required field in the CSV upload
        $result[] = $this->_email;

        if ($this->_mapping_hash[0]!="0")  $result[]    =   $this->_firstname;
        if ($this->_mapping_hash[1]!="0")  $result[]    =   $this->_lastname;
        if ($this->_mapping_hash[2]!="0")  $result[]    =   $this->_dob;
        if ($this->_mapping_hash[3]!="0")  $result[]    =   $this->_gender;
        if ($this->_mapping_hash[4]!="0")  $result[]    =   $this->_title;

        if ($this->_mapping_hash[5]!="0")  $result[]    =   $this->_website_created_on;
        if ($this->_mapping_hash[6]!="0")  $result[]    =   $this->_store_created_on;
        if ($this->_mapping_hash[7]!="0")  $result[]    =   $this->_account_created;
        if ($this->_mapping_hash[8]!="0")  $result[]    =   $this->_last_logged_in;
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

        if ($this->_mapping_hash[22]!="0") $result[]    =   $this->_total_orders;
        if ($this->_mapping_hash[23]!="0") $result[]    =   $this->_average_order_value;
        if ($this->_mapping_hash[24]!="0") $result[]    =   $this->_total_spend;
        if ($this->_mapping_hash[25]!="0") $result[]    =   $this->_last_order;
        if ($this->_mapping_hash[26]!="0") $result[]    =   $this->_last_order_no;
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
