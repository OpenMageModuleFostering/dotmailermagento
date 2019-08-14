<?php

class Dotdigitalgroup_Email_Adminhtml_Email_AutomationController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {

        $this->_title($this->__('Automation Studio'));
        $this->loadLayout();
        $this->_setActiveMenu('email_connector');

        // authorize or create token.
        $token = $this->generatetokenAction();
        $loginuserUrl = Dotdigitalgroup_Email_Helper_Config::API_CONNECTOR_URL_LOG_USER  . $token;

        $block = $this->getLayout()
            ->createBlock('core/text', 'connector_iframe')
            ->setText(
               "<iframe src=" . $loginuserUrl . " width=100% height=1400 frameborder='0' scrolling='no' style='margin:0;padding: 0;display:block;'></iframe>"
            );

        $this->_addContent($block);
        $this->renderLayout();
    }

    protected function _isAllowed(){

        return Mage::getSingleton('admin/session')->isAllowed('email_connector/automation_studio');
    }

    /**
     * Generate new token and connect from the admin.
     *
        POST httpsː//my.dotmailer.com/OAuth2/Tokens.ashx HTTP/1.1
        Content-Type: application/x-www-form-urlencoded
        client_id=QVNY867m2DQozogTJfUmqA%253D%253D&
        redirect_uri=https%3a%2f%2flocalhost%3a10999%2fcallback.aspx
        &client_secret=SndpTndiSlhRawAAAAAAAA%253D%253D
        &grant_type=authorization_code
     */
    public function generatetokenAction()
    {
        //check for secure url
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        $refreshToken = Mage::getSingleton('admin/user')->load($adminUser->getId())->getRefreshToken();

        if($refreshToken){
            $code = Mage::helper('connector')->getCode();
            $params = 'client_id=' . Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CLIENT_ID) .
                '&client_secret=' . Mage::getStoreConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CLIENT_SECRET_ID) .
                '&refresh_token=' . $refreshToken .
                '&grant_type=refresh_token';

            $url = Dotdigitalgroup_Email_Helper_Config::API_CONNECTOR_URL_TOKEN;
            Mage::helper('connector')->log('token  url : ' . $url . ' code : ' . $code);

            /**
             * Refresh Token request.
             */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

            $response = json_decode(curl_exec($ch));

            if (isset($response->error)) {
                Mage::helper('connector')->log("Token Error Num`ber:" . curl_errno($ch) . "Error String:" . curl_error($ch));
            }
            curl_close($ch);

            $token = $response->access_token;
            return $token;

        }else{
            Mage::getSingleton('adminhtml/session')->addNotice('Please Connect To Access The Page.');
        }

        $this->_redirect('*/system_config/edit', array('section' => 'connector_api_credentials'));
    }

    public function disconnectAction()
    {
        try {
            $adminUser = Mage::getSingleton('admin/session')->getUser();

            if ($adminUser->getRefreshToken()) {
                $adminUser->setRefreshToken()->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess('Successfully disconnected');
        }catch (Exception $e){
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer('*/*/*');
    }
}
