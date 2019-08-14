<?php

class Dotdigitalgroup_Email_Block_Recommended_Products extends Mage_Core_Block_Template
{
    /**
	 * Prepare layout, set the template.
	 * @return Mage_Core_Block_Abstract|void
	 */
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
	    //products to be diplayd for recommended pages
        $productsToDisplay = array();
        $orderId = $this->getRequest()->getParam('order_id');
	    //display mode based on the action name
        $mode  = $this->getRequest()->getActionName();
        $orderModel = Mage::getModel('sales/order')->load($orderId);
	    //number of product items to be displayed
        $limit      = Mage::helper('connector/recommended')->getDisplayLimitByMode($mode);
        $orderItems = $orderModel->getAllItems();
	    $numItems = count($orderItems);

	    //no product found to display
	    if ($numItems == 0 || ! $limit) {
		    return array();
	    }elseif (count($orderItems) > $limit) {
            $maxPerChild = 1;
        } else {
            $maxPerChild = number_format($limit / count($orderItems));
        }

		Mage::helper('connector')->log('DYNAMIC PRODUCTS : limit ' . $limit . ' products : ' . $numItems . ', max per child : '. $maxPerChild);

        foreach ($orderItems as $item) {
	        $i = 0;
            $productId = $item->getProductId();
            //parent product
            $productModel = Mage::getModel('catalog/product')->load($productId);
	        //check for product exists
            if ($productModel->getId()) {
	            //get single product for current mode
	            $recommendedProducts = $this->_getRecommendedProduct($productModel, $mode);
                foreach ($recommendedProducts as $product) {
	                //load child product
                    $product = Mage::getModel('catalog/product')->load($product->getId());
	                //check if still exists
	                if ($product->getId() && count($productsToDisplay) < $limit && $i <= $maxPerChild && $product->isSaleable() && !$product->getParentId()) {
		                //we have a product to display
                        $productsToDisplay[$product->getId()] = $product;
                        $i++;
                    }
                }
            }
	        //have reached the limit don't loop for more
            if (count($productsToDisplay) == $limit) {
                break;
            }
        }

        //check for more space to fill up the table with fallback products
        if (count($productsToDisplay) < $limit) {
            $fallbackIds = Mage::helper('connector/recommended')->getFallbackIds();

            foreach ($fallbackIds as $productId) {
                $product = Mage::getModel('catalog/product')->load($productId);
                if($product->isSaleable())
                    $productsToDisplay[$product->getId()] = $product;
                //stop the limit was reached
                if (count($productsToDisplay) == $limit) {
                    break;
                }
            }
        }

        Mage::helper('connector')->log('loaded product to display ' . count($productsToDisplay));
        return $productsToDisplay;
    }

	/**
	 * Product related items.
	 *
	 * @param Mage_Catalog_Model_Product $productModel
	 * @param $mode
	 *
	 * @return array
	 */
	private  function _getRecommendedProduct(Mage_Catalog_Model_Product $productModel, $mode)
    {
        //array of products to display
        $products = array();
        switch($mode){
            case 'related':
                $products = $productModel->getRelatedProducts();
                break;
            case 'upsell':
                $products = $productModel->getUpSellProducts();
                break;
            case 'crosssell':
                $products = $productModel->getCrossSellProducts();
                break;

        }

        return $products;
    }

	/**
	 * Diplay mode type.
	 *
	 * @return mixed|string
	 */
	public function getMode()
    {
        return Mage::helper('connector/recommended')->getDisplayType();

    }

	/**
	 * Number of the colums.
	 * @return int|mixed
	 * @throws Exception
	 */
	public function getColumnCount()
    {
        return Mage::helper('connector/recommended')->getDisplayLimitByMode($this->getRequest()->getActionName());
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