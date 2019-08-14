<?php

class Dotdigitalgroup_Email_Model_Sms_Campaign
{
    const UK_TELEPHONE_PATTERN = '/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/';

    private $_storeId;
    private $_status;
    private $_customerFirstName;
    private $_customerTelephone;

    private $_incrementId;

    private $_allsms = array(1, 2, 3, 4);
    private $_vars = array('/customer_name/', '/order_number/', '/{{var /', '/}}/');


    public function __construct($order)
    {
        $this->_storeId = $order->getStoreId();
        Mage::app()->setCurrentStore($this->_storeId);
        $billingAddress = $order->getBillingAddress();
        $this->_customerTelephone = $billingAddress->getTelephone();
        $this->_customerFirstName = $order->getCustomerFirstname();
        $this->_incrementId = $order->getIncrementId();
    }
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function sendSms()
    {
        $website = Mage::app()->getStore($this->_storeId)->getWebsite();
        $client = Mage::helper('connector')->getWebsiteApiClient($website);
        //all available sms in config
        foreach ($this->_allsms as $num) {
            $enabled = (bool)Mage::getStoreConfig(constant('Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SMS_ENABLED_' . $num));
            if($enabled) {
                $status = Mage::getStoreConfig(constant('Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SMS_STATUS_' . $num));
                $message = $this->_processMessage(Mage::getStoreConfig(constant('Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SMS_MESSAGE_' . $num)));
                $match = preg_match(self::UK_TELEPHONE_PATTERN, $this->_customerTelephone);
                if($match != false) {
                    $codePhone = preg_replace('/\A(0){1}+/', '+44', $this->_customerTelephone);
                    //status and telephone valid
                    if ($this->_status == $status) {
                        Mage::helper('connector')->log('sending sms message with status : ' . $status . ' and ' . $codePhone);
                        $client->postSmsMessagesSendTo($codePhone, $message);
                    }
                }else{
                    Mage::helper('connector')->log('SMS: phone not valid for UK : ' . $this->_customerTelephone);
                }
            }
        }
    }

    /**
     * @param $message
     * @return mixed
     */
    protected function _processMessage($message)
    {
        $replacemant = array();
        if(preg_match('/{{var/', $message)){
            $replacemant[] = $this->_customerFirstName;
            $replacemant[] = $this->_incrementId;
            $replacemant[] = '';
            $replacemant[] = '';
            $message = preg_replace($this->_vars, $replacemant,  $message);
        }
        return substr($message, 0, 160);
    }


}