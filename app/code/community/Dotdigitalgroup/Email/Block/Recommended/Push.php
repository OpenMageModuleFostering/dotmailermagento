<?php

class Dotdigitalgroup_Email_Block_Recommended_Push extends Mage_Core_Block_Template
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
    public function getLoadedProductCollection()
    {
        $productsToDisplay = array();
        $mode  = $this->getRequest()->getActionName();
        $limit = Mage::helper('connector/recommended')->getDisplayLimitByMode($mode);

        $productIds = Mage::helper('connector/recommended')->getProductPushIds();

        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('entity_id', array('in' => $productIds))
            ->setPageSize($limit)
        ;
        foreach ($productCollection as $_product) {
            $productId = $_product->getId();
            $product = Mage::getModel('catalog/product')->load($productId);
            if($product->isSaleable())
                $productsToDisplay[] = $product;

        }

        return $productsToDisplay;

    }


    public function getMode()
    {
        return Mage::helper('connector/recommended')->getDisplayType();

    }


    public function getPriceHtml($product)
    {
        $this->setTemplate('connector/product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }
}