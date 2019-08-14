<?php

class Dotdigitalgroup_Email_EmailController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        //Get current layout state
        $this->loadLayout();
        $this->renderLayout();
    }



    /**
     * Params
     * code - security check
     * order - order id
     * products type :
     *  1.related
     *  2.upsell
     *  3.cross sell
     *  4.best sellers
     *  5.most viewed
     *  6.recently viewed
     *
     */
    public function productsAction()
    {
        //get all params
        $params = $this->getRequest()->getParams();

        if(!isset($params['code']) || !isset($params['mode'])){

            exit();
        }
        //authenticate before proceed
        Mage::helper('connector')->auth($params['code']);
        Mage::register('mode', $params['mode']);
        if(isset($params['customer']))
            Mage::register('customer', $params['customer']);
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
        $this->loadLayout();
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