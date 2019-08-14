<?php

class Dotdigitalgroup_Email_Model_Connector_Test extends Dotdigitalgroup_Email_Model_Connector_Api_Client
{
    const TEST_API_USERNAME = 'apiuser-8e3b8f244ec9@apiconnector.com';
    const TEST_API_PASSWORD = 'Magento2013';
    const TEST_API_CAMPAIGN = '2643928';
    const TEST_CONTACT_ID   = '13';
    const TEST_CONTACT_EMAIL = 'ben.staveley@dotmailer.co.uk';

    public function validate($apiUsername, $apiPassword)
    {
        $this->setApiUsername($apiUsername);
        $this->setApiPassword($apiPassword);
        /**
         * Get Account Information
         */
        $accountInfo = $this->getAccountInfo();
        $this->_sendInstallConfirmation();

        return $accountInfo;
    }

    public function ajaxvalidate($apiUsername, $apiPassword)
    {
        $message = 'Credentials Valid.';
        $this->setApiUsername($apiUsername);
        $this->setApiPassword($apiPassword);
        $response = $this->getAccountInfo();

        if(isset($response->message)){
            $message = 'API Username And API Password Do Not Match!';
        }
        return $message;
    }

    private function _sendInstallConfirmation()
    {
        // set test credentials
        $this->setApiUsername(self::TEST_API_USERNAME);
        $this->setApiPassword(self::TEST_API_PASSWORD);

        $testEmail           = self::TEST_CONTACT_EMAIL;
        $contactId           = self::TEST_CONTACT_ID;
        $campaignId          = self::TEST_API_CAMPAIGN;

        /**
         * send initial info
         */
        $this->sendIntallInfo($testEmail, $contactId, $campaignId);
    }

    public function createDefaultDataFields(){


        $helper = Mage::helper('connector');

        foreach ($helper->getDefaultDataFields() as $datafield){

            $this->postDataFields($datafield);
        }
        return ;

    }
}
