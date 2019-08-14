<?php

class Dotdigitalgroup_Email_Adminhtml_Email_ContactController extends Mage_Adminhtml_Controller_Action
{
    /**
     * constructor - set the used module name
     */
    protected function _construct(){
        $this->setUsedModuleName('Dotdigitalgroup_Email');
    }

    public function indexAction(){
        $this->_title($this->__('Email'))
            ->_title($this->__('Manage Contacts'));
        $this->loadLayout();
        $this->_setActiveMenu('email_connector');
        $this->renderLayout();
    }

    public function newAction()
    {
        // We just forward the new action to a blank edit form
        $this->_forward('edit');
    }


    public function editAction()
    {
        $contactId  = (int) $this->getRequest()->getParam('id');
        $contact = $this->_initAction();
        if ($contactId && !$contact->getId()) {
            $this->_getSession()->addError(Mage::helper('connector')->__('This contact no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        if ($data = Mage::getSingleton('adminhtml/session')->getContactData(true)){
            $contact->setData($data);
        }
        Mage::dispatchEvent('email_contact_controller_edit_action', array('contact' => $contact));
        $this->loadLayout();
        if ($contact->getId()){
            if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
                $switchBlock->setDefaultStoreName(Mage::helper('connector')->__('Default Values'))
                    ->setSwitchUrl($this->getUrl('*/*/*', array('_current'=>true, 'active_tab'=>null, 'tab' => null, 'store'=>null)));
            }
        }else{
            $this->getLayout()->getBlock('left')->unsetChild('store_switcher');
        }
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function saveAction(){
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $contactId      = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPost();
        if ($data) {
            $contact    = $this->_initAction();

            $contactData = $this->getRequest()->getPost('contact', array());
            $contact->addData($contactData);

            try {
                $contact->save();
                $contactId = $contact->getId();
                $this->_getSession()->addSuccess(Mage::helper('connector')->__('Contact was saved.'));
            }catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage())
                    ->setContactData($contactData);
                $redirectBack = true;
            }
            catch (Exception $e){
                Mage::logException($e);
                $this->_getSession()->addError(Mage::helper('connector')->__('Error saving contact'))
                    ->setContactData($contactData);
                $redirectBack = true;
            }
        }
        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id'    => $contactId,
                '_current'=>true
            ));
        }
        else {
            $this->_redirect('*/*/', array('store'=>$storeId));
        }
    }

    public function deleteAction(){
        if ($id = $this->getRequest()->getParam('id')) {
            $contact = Mage::getModel('email_connector/contact')->load($id);
            try {
                $contact->delete();
                $this->_getSession()->addSuccess(Mage::helper('connector')->__('The contact has been deleted.'));
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store'))));
    }

    public function massDeleteAction() {
        $contactIds = $this->getRequest()->getParam('contact');
        if (!is_array($contactIds)) {
            $this->_getSession()->addError($this->__('Please select contacts.'));
        }else {
            try {
                foreach ($contactIds as $contactId) {
                    $contact = Mage::getSingleton('email_connector/contact')->load($contactId);
                    Mage::dispatchEvent('connector_controller_affiliate_delete', array('contact' => $contact));
                    $contact->delete();
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('connector')->__('Total of %d record(s) have been deleted.', count($contactIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    public function gridAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _initAction(){
        $this->_title($this->__('Newsletter'))
            ->_title($this->__('Manage Contacts'));

        $contactId  = (int) $this->getRequest()->getParam('id');
        $contact    = Mage::getModel('email_connector/contact')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if ($contactId) {
            $contact->load($contactId);
        }
        Mage::register('current_contact', $contact);
        return $contact;
    }

    public function exportCsvAction(){
        $fileName   = 'contacts.csv';
        $content    = $this->getLayout()->createBlock('email_connector/adminhtml_contact_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('newsletter/email_connector/email_connector_contact');
    }

}
