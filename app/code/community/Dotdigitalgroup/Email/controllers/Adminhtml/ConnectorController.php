<?php

class Dotdigitalgroup_Email_Adminhtml_ConnectorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Create and map datafields
     */
    public function setupdatafieldsAction()
    {
        $result = array('errors' => false, 'message' => '');
        $apiModel = Mage::getModel('connector/connector_api_client');
        $redirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'connector_data_field_settings'));

        // get all possible datatifileds
        $datafields = Mage::helper('connector')->getDatafields();
        foreach ($datafields as $key => $datafield){
            $responce = $apiModel->postDataFields($datafield);

            if(isset($responce->message)){
                $result['errors'] = true;
                $result['message'] .=  'Datafield ' . $datafield['name'] . ' - '. $responce->message . '</br>';
            }else{
                //map the succesful created datafield
                $config = new Mage_Core_Model_Config();
                //@todo check scope
                $config->saveConfig('connector_data_field_settings/customer_data/' . $key, strtoupper($datafield['name']));

                // clean config cache
                Mage::getConfig()->cleanCache();

                Mage::helper('connector')->log('successfully connected : ' . $datafield['name']);
            }
        }
        if($result['errors'])
            Mage::getSingleton('adminhtml/session')->addNotice($result['message']);
        else{
            Mage::getSingleton('adminhtml/session')->addSuccess('All Datafields Created And Mapped.');
        }

        $this->_redirectUrl($redirectUrl);
    }

    public function connectAction()
    {
        $params = $this->getRequest()->getParams();
        $apiModel = Mage::getModel('connector/api');
        $helper = Mage::helper('connector');
        $testModel = Mage::getModel('connector/connector_test');

            if(isset($params['store'])){

                //store level
                $store = $params['store'];
                $apiUsername = $helper->getApiUsername($store);
                $apiPassword = $helper->getApiPassword($store);
                $store = Mage::getModel('core/store')->load($store);


                $apiModel->setStoreId($store->getId());
            }elseif(isset($params['website'])){
                //website level
                $website = $params['website'];
                $apiUsername = $helper->getApiUsername($website, 'website');
                $apiPassword = $helper->getApiPassword($website, 'website');
                $website = Mage::getModel('core/website')->load($website);
                $apiModel->setWebsiteId($website->getId());
                $helper->log('connect website account');
            }else{
                //admin level
                $apiUsername = $helper->getApiUsername();
                $apiPassword = $helper->getApiPassword();
                $helper->log('connect default account');
            }

            $testResult = $testModel->validate($apiUsername, $apiPassword);
            if(isset($testResult->message)){
                $helper->log("VALIDATE " . $testResult->message);
            }else{

                // save api data
                $apiModel->setApiUsername($apiUsername)
                    ->setModifiedAt(Varien_Date::now())
                    ->setData('api_password', $apiPassword);

                $apiModel->save();
            }

        $this->_redirectReferer();
    }


    public function forcecustomersyncAction()
    {
        $website    = $this->getRequest()->getParam('website');
        $store      = $this->getRequest()->getParam('store');

        $result = Mage::getModel('connector/customer_customer')->forceSync($store, $website);


        Mage::getSingleton('adminhtml/session')->addSuccess('Number of Customers : ' . $result['customers'] . ', Subscribers : ' . $result['subscribers']);

        $this->_redirectReferer();
    }

    public function resetordersAction()
    {
        $emailOrders = Mage::getModel('connector/email_order')->getCollection()
            ->addFieldToFilter('email_imported', array('notnull' => true));

        try{
            foreach ($emailOrders as $order) {
                $order->setEmailImported(null)->save();
            }

        }catch(Exception $e){
            Mage::helper('connector')->log($e->getMessage());
        }
        Mage::getSingleton('adminhtml/session')->addSuccess('Done.');

        $this->_redirectReferer();
    }

    public function suppresscontactsAction()
    {
        Mage::helper('connector')->log('start force suppress');
        //forcesync subscribers
        Mage::getModel('connector/newsletter_subscriber')
            ->unsubscribe(true);
        Mage::helper('connector')->log('end  force suppresssed sync.');
        Mage::getSingleton('adminhtml/session')->addSuccess('Done.');
        $this->_redirectReferer();

    }

}