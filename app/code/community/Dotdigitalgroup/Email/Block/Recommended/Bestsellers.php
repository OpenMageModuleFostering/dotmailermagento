<?php

class Dotdigitalgroup_Email_Block_Recommended_Bestsellers extends Mage_Core_Block_Template
{

    /**
	 * Prepare layout.
	 * @return Mage_Core_Block_Abstract|void
	 */
	protected function _prepareLayout()
    {
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');
        }
    }

	/**
	 * Get product collection.
	 * @return array
	 * @throws Exception
	 */
	public function getLoadedProductCollection()
    {
        $productsToDisplay = array();
        $mode = $this->getRequest()->getActionName();
        $limit  = Mage::helper('connector/recommended')->getDisplayLimitByMode($mode);
        $from  =  Mage::helper('connector/recommended')->getTimeFromConfig($mode);
	    $to = Zend_Date::now()->toString(Zend_Date::ISO_8601);

	    $productCollection = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty($from, $to)
            ->addAttributeToSelect(array('name', 'price', 'small_image'))
            ->setPageSize($limit)
            ->setOrder('ordered_qty', 'desc');

        foreach ($productCollection as $_product) {
            $productId = $_product->getId();
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->isSalable())
                $productsToDisplay[] = $product;
        }


	    return $productsToDisplay;
    }

	/**
	 * Display type mode.
	 *
	 * @return mixed|string
	 */
	public function getMode()
    {
        return Mage::helper('connector/recommended')->getDisplayType();

    }

	/**
	 * Price html.
	 * @param $product
	 *
	 * @return string
	 */
	public function getPriceHtml($product)
    {
        $this->setTemplate('connector/product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

}