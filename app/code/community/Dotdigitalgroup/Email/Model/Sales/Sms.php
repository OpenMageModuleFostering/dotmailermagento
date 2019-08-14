<?php

class Dotdigitalgroup_Email_Model_Sales_Sms
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
        $client = Mage::getModel('connector/connector_api_client');
        $helper = Mage::helper('connector');

        $message = Mage::getStoreConfig(constant('self::XML_PATH_SMS_MESSAGE_' . $number), $storeId);

        $message = $this->_processVariables($order, $message);
        $pattern = "/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/";

        $match = preg_match($pattern, $customerTelephone);

        if ($match != false) {
            $telephoneNumber = preg_replace('/\A(0){1}+/', '+44', $customerTelephone);
            $client->setApiUsername($helper->getApiUsername($storeId));
            $client->setApiPassword($helper->getApiPassword($storeId));
            $client->postSmsMessagesSendTo($telephoneNumber, $message);
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
            $helper->log($message);
        }

        return $message;
    }

}