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
}