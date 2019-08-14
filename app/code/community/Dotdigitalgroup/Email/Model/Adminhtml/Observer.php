<?php

class Dotdigitalgroup_Email_Model_Adminhtml_Observer
{

    /**
     * API Sync and Data Mapping.
     * Reset contacts for reimport.
     * @return $this
     */
    public function actionConfigResetContacts()
    {
        $contactModel = Mage::getModel('email_connector/contact');
        $numImported = $contactModel->getNumberOfImportedContacs();
        $updated = $contactModel->resetAllContacts();
        Mage::helper('connector')->log('-- Imported contacts: ' . $numImported  . ' reseted :  ' . $updated . '--');

        return $this;
    }

    /**
     * API Transactional Section.
     * Default data fields for transactional account.
     * @return $this
     */
    public function actionConfigTransactional()
    {
        $client = Mage::getModel('email_connector/apiconnector_client');
        $username = Mage::helper('connector/transactional')->getApiUsername();
        $password = Mage::helper('connector/transactional')->getApiPassword();
        $client->setApiUsername($username)->setApiPassword($password);
        $dataFields = Mage::getModel('email_connector/connector_datafield')->getTransactionalDefaultDatafields();

        foreach ($dataFields as $field) {
            //create the datafields
            $client->postDataFields($field);
        }
        return $this;
    }

    /**
     * API Credentials.
     * Installation and validation confirmation.
     * @return $this
     */
    public function actionConfigSaveApi()
    {
        $groups = Mage::app()->getRequest()->getPost('groups');
        if (isset($groups['api']['fields']['username']['inherit']) || isset($groups['api']['fields']['password']['inherit']))
            return $this;

        $apiUsername =  isset($groups['api']['fields']['username']['value'])? $groups['api']['fields']['username']['value'] : false;
        $apiPassword =  isset($groups['api']['fields']['password']['value'])? $groups['api']['fields']['password']['value'] : false;
        //skip if the inherit option is selected
        if ($apiUsername && $apiPassword) {
            Mage::helper('connector')->log('----VALIDATING ACCOUNT---');
            $testModel = Mage::getModel('email_connector/apiconnector_test');
            $isValid = $testModel->validate($apiUsername, $apiPassword);
            if ($isValid) {
                /**
                 * Create account contact datafields
                 */
                $client = Mage::getModel('email_connector/apiconnector_client');
	            $client->setApiUsername($apiUsername)
                    ->setApiPassword($apiPassword);
                $datafields = Mage::getModel('email_connector/connector_datafield')->getDefaultDataFields();
                foreach ($datafields as $datafield) {
                    $client->postDataFields($datafield);
                }
                /**
                 * Send install info
                 */
                $testModel->sendInstallConfirmation();
            } else {
                /**
                 * Disable invalid Api credentials
                 */
                $scopeId = 0;
                if ($website = Mage::app()->getRequest()->getParam('website')) {
                    $scope = 'websites';
                    $scopeId = Mage::app()->getWebsite($website)->getId();
                } else {
                    $scope = "default";
                }
                $config = Mage::getConfig();
                $config->saveConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_ENABLED, 0, $scope, $scopeId);
                $config->cleanCache();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('connector')->__('API Credentials Valid.'));
        }
        return $this;
    }
}