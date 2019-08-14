<?php

class Dotdigitalgroup_Email_Model_Sales_Sms extends Dotdigitalgroup_Email_Model_Api_Rest
{
    const XML_PATH_SMS_MESSAGE_ONE_STATUS        = 'sms_section/sms_message_one/order_status';
    const XML_PATH_SMS_MESSAGE_TWO_STATUS        = 'sms_section/sms_message_two/order_status';
    const XML_PATH_SMS_MESSAGE_THREE_STATUS      = 'sms_section/sms_message_three/order_status';
    const XML_PATH_SMS_MESSAGE_FOUR_STATUS       = 'sms_section/sms_message_four/order_status';

    const XML_PATH_SMS_MESSAGE_ONE               = 'sms_section/sms_message_one/message';
    const XML_PATH_SMS_MESSAGE_TWO               = 'sms_section/sms_message_two/message';
    const XML_PATH_SMS_MESSAGE_THREE             = 'sms_section/sms_message_three/message';
    const XML_PATH_SMS_MESSAGE_FOUR              = 'sms_section/sms_message_four/message';

    public $_available = array('/customer_name/', '/order_number/', '/{{var /', '/}}/');

    public function sendMessage($order, $number)
    {
        $storeId    = $order->getStoreId();
        $billing    = $order->getBillingAddress();
        $customerTelephone = $billing->getTelephone();

        $message = Mage::getStoreConfig(constant('self::XML_PATH_SMS_MESSAGE_' . $number), $storeId);

        $message = $this->_processVariables($order, $message);


        $pattern = "/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/";

        $match = preg_match($pattern, $customerTelephone);
        $this->_helper->log('sms number to send : ' . $customerTelephone, null, $this->_log_filename);

        if ($match != false) {
            $telephoneNumber = preg_replace('/\A(0){1}+/', '+44', $customerTelephone);
            Mage::helper('connector')->log($telephoneNumber, null, 'api.log');
            $this->postSmsMessagesSendTo($telephoneNumber, $message);
        } else {

            $this->_helper->log('telephone number not valid ' . $customerTelephone, null, $this->_log_filename);
        }
    }

    /**
     * @param $order
     * @param $message
     * @return mixed
     */
    protected function _processVariables($order, $message)
    {
        $helper = Mage::helper('connector');
        if(preg_match('/{{var/', $message)){

            $firstname = $order->getCustomerFirstname();

            $replacemant = array();
            $replacemant[] = $firstname;
            $replacemant[] = $order->getIncrementId();
            $replacemant[] = '';
            $replacemant[] = '';

            $message = preg_replace($this->_available, $replacemant,  $message);

            $helper->log($message, null, $this->_log_filename);

        }

        return $message;
    }

}