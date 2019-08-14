<?php

class Dotdigitalgroup_Email_Adminhtml_Email_DashboardController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Dashboard'));

        $this->loadLayout();
        $this->_setActiveMenu('newsletter');
        $this->_addContent($this->getLayout()->createBlock('email_connector/adminhtml_dashboard'));
        $this->renderLayout();
    }
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('email_connector_dashboard');
    }
}
