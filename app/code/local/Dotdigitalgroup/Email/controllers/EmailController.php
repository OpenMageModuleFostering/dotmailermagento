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
        //@todo allow from these Ips
        $allowedIps = array(

        );

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

    public function resetimportedorderdataAction()
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        foreach ($orderCollection as $one) {

            try{

                $one->setData('dotmailer_order_imported', null);
                $one->save();
            }catch(Exception $e){
                Mage::logException($e);
            }
        }

    }

    public function saveMissingIdCustomerAction()
    {
        $customer = Mage::getModel('connector/customer_customer')->getMissingContacts();
        $helper = Mage::helper('connector');
        $helper->log('number of miss ids : ' . count($customer));
        foreach ($customer as $one) {
            try{
                $helper->log($one->getEmail());
                $one->save();
            }catch(Exception $e){
                $helper->log($e->getMessage());

            }

        }

    }

    public function importAllTransactionalDataAction()
    {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $helper = Mage::helper('connector');
        $helper->log('import all transactional data : ' . $orderCollection->getSize());
        foreach($orderCollection as $one){

            $order = Mage::getModel('connector/connector_order', $one);
            if($order->connector_id)
                $orders[] = $order;
        }
        $helper->log('orders created : ' . count($orders));
        $rest = Mage::getModel('connector/api_rest');

        try{
            $result = $rest->sendMultiTransactionalData($orders, 'Order');
            $helper->log($result);
        }catch(Exception $e){
            $helper->log($e->getMessage());
        }

    }
}