<?php

class Dotdigitalgroup_Email_EmailController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        //Get current layout state
        $this->loadLayout();
        $this->renderLayout();
    }

    public function couponAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function basketAction()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $this->loadLayout();
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');
        }
        $basket = $this->getLayout()->createBlock('email_connector/basket', 'connector_basket', array(
            'template' => 'connector/basket.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($basket);
        $this->renderLayout();
    }

    public function logAction()
    {
        //file name param
        $file = $this->getRequest()->getParam('file');
        $fileName = $file . '.log';
        $filePath = Mage::getBaseDir('log') . DIRECTORY_SEPARATOR . $fileName;

        $this->_prepareDownloadResponse($fileName, array(
            'type'  => 'filename',
            'value' => $filePath
        ));
        exit();
    }

    public function callbackAction()
    {
        $code = $this->getRequest()->getParam('code', false);
        $userId = $this->getRequest()->getParam('state');
        $adminUser = Mage::getModel('admin/user')->load($userId);
        $callback = 'https://dotmailerformagento.co.uk/magentotesting/connector/email/callback';

        if($code){
            $data = 'client_id='    . Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CLIENT_ID) .
                '&client_secret='   . Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CLIENT_SECRET_ID) .
                '&redirect_uri='    . $callback .
                '&grant_type=authorization_code' .
                '&code='            . $code;


            $url = Dotdigitalgroup_Email_Helper_Config::API_CONNECTOR_URL_TOKEN;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/x-www-form-urlencoded'));


            $response = json_decode(curl_exec($ch));
            if($response === false)
            {
                echo "Error Number:".curl_errno($ch)."<br>";
                echo "Error String:".curl_error($ch);
            }

            $adminUser->setRefreshToken($response->refresh_token)->save();
        }

        //@todo redirect to settings if the authorisation fails.
        //$this->_redirectReferer(Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'connector_api_credentials')));
        $this->_redirectReferer(Mage::helper('adminhtml')->getUrl('adminhtml/email_automation/index'));
    }
}