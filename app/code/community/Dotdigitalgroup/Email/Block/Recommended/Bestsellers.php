<?php

class Dotdigitalgroup_Email_Block_Recommended_Bestsellers extends Mage_Core_Block_Template
{

    protected function _prepareLayout()
    {
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');
        }
    }


    public function getLoadedProductCollection()
    {
        $productsToDisplay = array();
        $mode = $this->getRequest()->getActionName();
        $limit  = Mage::helper('connector/recommended')->getDisplayLimitByMode($mode);
        $from  =  Mage::helper('connector/recommended')->getTimeFromConfig($mode);
        $to = Zend_Date::now()->toString(Zend_Date::ISO_8601);

        $productCollection = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty($from, $to)
            ->addAttributeToSelect('*')
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setPageSize($limit)
            ->setOrder('ordered_qty', 'desc');
        foreach ($productCollection as $_product) {
            $productId = $_product->getId();
            $product = Mage::getModel('catalog/product')->load($productId);
            if($product->isSalable())
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