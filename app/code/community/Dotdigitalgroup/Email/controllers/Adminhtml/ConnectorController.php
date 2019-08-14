<?php

class Dotdigitalgroup_Email_Adminhtml_ConnectorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * AutoCreate and map datafields.
     */
    public function setupdatafieldsAction()
    {
        $result = array('errors' => false, 'message' => '');
        $apiModel = Mage::getModel('email_connector/apiconnector_client');
        $redirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'connector_data_mapping'));

        // get all possible datatifileds
        $datafields = Mage::getModel('email_connector/connector_datafield')->getContactDatafields();
        foreach ($datafields as $key => $datafield) {
            $response = $apiModel->postDataFields($datafield);

            //ignore existing datafields message
            if (isset($response->message) && $response->message != Dotdigitalgroup_Email_Model_Apiconnector_Client::REST_API_DATAFILEDS_EXISTS) {
                $result['errors'] = true;
                $result['message'] .=  ' Datafield ' . $datafield['name'] . ' - '. $response->message . '</br>';
            } else {
                $website = $this->getRequest()->getParam('website', false);
                if ($website) {
                    $scope = 'website';
                    $scopeId = Mage::app()->getWebsite($website)->getId();
                } else {
                    $scope = 'default';
                    $scopeId = '0';
                }
                /**
                 * map the succesful created datafield
                 */
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('connector_data_mapping/customer_data/' . $key, strtoupper($datafield['name']), $scope, $scopeId);
                Mage::helper('connector')->log('successfully connected : ' . $datafield['name']);
            }
        }
        if ($result['errors']) {
            Mage::getSingleton('adminhtml/session')->addNotice($result['message']);
        } else {
            Mage::getConfig()->cleanCache();
            Mage::getSingleton('adminhtml/session')->addSuccess('All Datafields Created And Mapped.');
        }

        $this->_redirectUrl($redirectUrl);
    }

    /**
     * Reset order for reimport.
     */
    public function resetordersAction()
    {
        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $coreResource->getConnection('core_write');
        try{
            $num = $conn->update($coreResource->getTableName('email_connector/order'),
                array('email_imported' => new Zend_Db_Expr('null')),
                $conn->quoteInto('email_imported is ?', new Zend_Db_Expr('not null'))
            );
        }catch (Exception $e){
            Mage::logException($e);
        }
        Mage::helper('connector')->log('-- Reset Orders for reimport : ' . $num);
        Mage::getSingleton('adminhtml/session')->addSuccess('Done.');
        $this->_redirectReferer();
    }

    /**
     * Refresh suppressed contacts.
     */
    public function suppresscontactsAction()
    {
        Mage::getModel('email_connector/newsletter_subscriber')
            ->unsubscribe(true);
        Mage::getSingleton('adminhtml/session')->addSuccess('Done.');
        $this->_redirectReferer();

    }
    /**
     * Remove contact id's.
     */
    public function deletecontactidsAction()
    {
        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $coreResource->getConnection('core_write');
        try{
            $num = $conn->update($coreResource->getTableName('email_connector/contact'),
                array('contact_id' => new Zend_Db_Expr('null')),
                $conn->quoteInto('contact_id is ?', new Zend_Db_Expr('not null'))
            );
        }catch (Exception $e){
            Mage::logException($e);
        }
        Mage::getSingleton('adminhtml/session')->addSuccess('Number Of Contacts Id Removed: '. $num);
        $this->_redirectReferer();
    }
    public function ajaxvalidationAction()
    {
        $params = $this->getRequest()->getParams();
        $apiUsername     = $params['api_username'];
        $apiPassword     = $params['api_password'];
        $message = Mage::getModel('email_connector/apiconnector_test')->ajaxvalidate($apiUsername, $apiPassword);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($message));
    }

    public function createnewdatafieldAction()
    {
        $params = $this->getRequest()->getParams();
        $website = $this->getRequest()->getParam('website', 0);
        $client = Mage::helper('connector')->getWebsiteApiClient($website);
        if (strlen($params['name'])) {
            $response = $client->postDataFields($params['name'], $params['type']);
            if (isset($response->message)) {
                Mage::getSingleton('adminhtml/session')->addError($response->message);
                Mage::helper('connector')->log($response->message);
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess('Datafield created : ' . $params['name']);
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Datafield cannot be empty.');
        }
    }

    public function createnewaddressbookAction()
    {
        $addressBookName = $this->getRequest()->getParam('name');
        $website  = $this->getRequest()->getParam('website', 0);
        $client = Mage::helper('connector')->getWebsiteApiClient($website);
        if (strlen($addressBookName)) {
            $response = $client->PostAddressBooks($addressBookName);
            if(isset($response->message))
                Mage::getSingleton('adminhtml/session')->addError($response->message);
            else
                Mage::getSingleton('adminhtml/session')->addSuccess('Address book : '. $addressBookName . ' created.');
        }

    }

    public function reimoprtsubscribersAction()
    {
        $updated = Mage::getModel('email_connector/contact')->resetSubscribers();
        if ($updated) {
            Mage::getSingleton('adminhtml/session')->addSuccess('Subscribers updated : ' . $updated);
        } else {
            Mage::getSingleton('adminhtml/session')->addNotice('No subscribers imported!');
        }
        $this->_redirectReferer();
    }
}