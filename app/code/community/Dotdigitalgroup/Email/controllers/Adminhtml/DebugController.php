<?php

class Dotdigitalgroup_Email_Adminhtml_DebugController extends Mage_Adminhtml_Controller_Action
{
    public function deletecontactidsAction()
    {
        $contactCollection = Mage::getModel('connector/email_contact')->getCollection()
            ->addFieldToFilter('contact_id', array('neq' => null))
        ;
        $numUpdated = 0;

        foreach($contactCollection as $contact){

            try{

                $contact->setContactId(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_NOT_IMPORTED)->save();
            }catch (Exception $e){
                Mage::helper('connector')->log($e->getMessage());
            }
            $numUpdated++;
        }

        if($numUpdated)
            Mage::getModel('adminhtml/session')->addSuccess('Number Of Contacts Id Removed: '. $numUpdated);
        $this->_redirectReferer();
    }

    public function countcontactsAction()
    {
        $total = Mage::getModel('customer/customer')->getCollection()->getSize();

        $contactsSize = Mage::helper('connector')->countCustomersWithContactId();

        Mage::getModel('adminhtml/session')->addSuccess('Customers Total No: ' . $total . '</br>  Customers With Contact Id No: ' . $contactsSize);
        $this->_redirectReferer();
    }

    public function ajaxvalidationAction()
    {
        $params = $this->getRequest()->getParams();

        $apiUsername     = $params['api_username'];
        $apiPassword     = $params['api_password'];
        $message = Mage::getModel('connector/connector_test')->ajaxvalidate($apiUsername, $apiPassword);

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($message));
    }
}
