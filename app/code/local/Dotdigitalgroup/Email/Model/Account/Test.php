<?php

class Dotdigitalgroup_Email_Model_Account_Test extends Dotdigitalgroup_Email_Model_Api_Rest
{
    const TEST_API_USERNAME = 'apiuser-2c692ba1bbd2@apiconnector.com';
    const TEST_API_PASSWORD = 'Magento2013';
    const TEST_API_CAMPAIGN = '2643928';
    const TEST_CONTACT_ID   = '13';
    const TEST_CONTACT_EMAIL = 'ben.staveley@dotmailer.co.uk';

    public function test($api_user = null, $api_password = null)
    {
        if($api_user && $api_password){
            $this->_api_user = $api_user;
            $this->_api_password = $api_password;
        }
        $response = array('errors' => 0, 'message' => '');
        $result = $this->testAccount();
        if(!$result){
            $response['errors'] = true;
            $response['message'] = 'Invalid API Credentials.';
        }
        $this->sendInstallConfirmation();

        return $response;
    }

    public function sendInstallConfirmation()
    {
        $this->_api_user     = self::TEST_API_USERNAME;
        $this->_api_password = self::TEST_API_PASSWORD;
        $testEmail           = self::TEST_CONTACT_EMAIL;
        $contactId           = self::TEST_CONTACT_ID;
        $campaignId          = self::TEST_API_CAMPAIGN;

        // send initial info
        $this->sendIntallInfo($testEmail, $contactId, $campaignId);

    }
}
