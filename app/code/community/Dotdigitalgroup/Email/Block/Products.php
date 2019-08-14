<?php

class Dotdigitalgroup_Email_Block_Products extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');
        }
    }

    /**
     * get the products to display for table
     */
    public function getRecommendedProducts()
    {
        $productsToDisplay = array();
        $orderId = $this->getRequest()->getParam('order', false);
        $mode  = $this->getRequest()->getParam('mode', false);
        if($orderId && $mode){
            $orderModel = Mage::getModel('sales/order')->load($orderId);
            if($orderModel->getId()){
                Mage::app()->setCurrentStore($orderModel->getStoreId());
                //order products
                $productRecommended = Mage::getModel('email_connector/dynamic_recommended', $orderModel);
                $productRecommended->setMode($mode);

                //get the order items recommendations
                $productsToDisplay = $productRecommended->getProducts();
            }
        }

        return $productsToDisplay;
    }


    public function getPriceHtml($product)
    {
        $this->setTemplate('connector/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

    public function getDisplayType()
    {
        return Mage::helper('connector/recommended')->getDisplayType();

    }
}