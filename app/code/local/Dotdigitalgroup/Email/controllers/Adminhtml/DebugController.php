<?php

class Dotdigitalgroup_Email_Adminhtml_DebugController extends Mage_Adminhtml_Controller_Action
{

    public function forcecustomersyncAction()
    {
        $result = Mage::getModel('connector/customer_customer')->sync();

        if ($result['error']) {
            Mage::getSingleton('adminhtml/session')->addError($result['message']);
        }else {
            Mage::getSingleton('adminhtml/session')->addSuccess($result['message']);
        }
        $this->_redirectReferer();
    }

    public function forcelostbasketsyncAction()
    {

        $result = Mage::getModel('connector/sales_quote')->forceProccess();

        if ($result['errors'])
            Mage::getSingleton('adminhtml/session')->addError($result['message']);
        else
            Mage::getSingleton('adminhtml/session')->addSuccess($result['message']);

        $this->_redirectReferer();
    }

    public function forcesuppressedAction()
    {
        // forse sync
        $result = Mage::getModel('connector/customer_suppressed')->unsubscribe(true);

        if($result['errors']){
            Mage::getSingleton('adminhtml/session')->addError($result['message']);
        }else{

            if($result['customers'] > 0)
                Mage::getSingleton('adminhtml/session')->addSuccess('Customers Unsubscribed : ' . $result['customers']);
            else
                Mage::getSingleton('adminhtml/session')->addSuccess('Done.');
        }

        $this->_redirectReferer();
    }

    public function testCredentialsAction()
    {
        /**
         * Test account API
         */
        $testResult = Mage::getModel('connector/account_test')->test();

        if($testResult['errors']){
            Mage::getSingleton('adminhtml/session')->addError($testResult['message']);
        }else{
            Mage::getSingleton('adminhtml/session')->addSuccess('API Credentials Valid.');
        }
        $this->_redirectReferer();
    }
    public function transactionalSyncAction()
    {

        Mage::register('force_transactional', true);
        $initialSync = Mage::getModel('connector/sales_order')->sync();

        if ($initialSync['errors'])
            Mage::getSingleton('adminhtml/session')->addError($initialSync['message']);
        else
            Mage::getSingleton('adminhtml/session')->addSuccess($initialSync['message']);

        $this->_redirectReferer();
    }
    public function deletecontactsidAction()
    {
        Mage::register('first_time_sync', true);
        $customerModel = new Dotdigitalgroup_Email_Model_Customer_Customer();
        $customers = $customerModel->getContactsCustomers();
        $numUpdated = 0;
        if($customers->getSize()){
            foreach ($customers as $one){
                try{
                    $customer = Mage::getModel('customer/customer')->load($one->getId());
                    $customer->setData('dotmailer_contact_id', null);
                    $customer->save();
                }catch(Exception $e){
                    Mage::helper('connector')->log($e->getMessage(), null, 'api.log');
                }
                $numUpdated++;
            }
        }
        Mage::unregister('first_time_sync');
        if($numUpdated);
            Mage::getModel('adminhtml/session')->addSuccess('Number Of Contacts Id Removed :'. $numUpdated);
        $this->_redirectReferer();
    }

    public function countcontactsAction()
    {
        $customer = new Dotdigitalgroup_Email_Model_Customer_Customer();
        $contacts = $customer->getContactsCustomers();
        $total = $customer->getTotalNumberCustomers();
        $numMissing = $contacts->getSize();

        Mage::getModel('adminhtml/session')->addSuccess('Customers No: ' . $total . ',  With Contact Id No: ' . $numMissing);
        $this->_redirectReferer();
    }

    public function ajaxvalidationAction()
    {
        $result = 'Validation failed!';
        $api_user = $this->getRequest()->getParam('api_user');
        $api_password = $this->getRequest()->getParam('api_password');
        $testResult = Mage::getModel('connector/account_test')->test($api_user, $api_password);
        if($testResult['errors'] == false){
            $result  = 'Valid';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }


}
