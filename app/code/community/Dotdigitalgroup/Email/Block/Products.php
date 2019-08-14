<?php

class Dotdigitalgroup_Email_Block_Products extends Mage_Core_Block_Template
{
    /**
     * get the products to display for table
     */
    public function getRecommendedProducts()
    {
        //get all params
        $params = $this->getRequest()->getParams();
        $productsToDisplay = array();

        if(isset($params['customer'])){
            $productRecommended = new Dotdigitalgroup_Email_Model_Dynamic_Recommended();
            $productsToDisplay = $productRecommended->getProducts();
            $orderId = false;

        }else{

            $orderId = $params['order'];
        }



        if($orderId){
            $orderModel = Mage::getModel('sales/order')->load($orderId);
            if($orderModel->getId()){
                //order products
                $productRecommended = new Dotdigitalgroup_Email_Model_Dynamic_Recommended($orderModel);

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

    public function getMode()
    {
        return Mage::helper('connector/recommended')->getMode();

    }
}