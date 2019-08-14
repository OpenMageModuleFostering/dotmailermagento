<?php

class Dotdigitalgroup_Email_Model_Dynamic_Recommended extends Dotdigitalgroup_Email_Model_Dynamic_Product_Mapper
{
    protected $_result_limit;

    protected $_storeId;//order store id

    protected $_order_items = 0;//order product items

    protected $_num_order_items;//number of items from order

    protected $_productMapper;

    protected $_is_log_enabled;

    protected $_helper;

    /**
     * @param $orderModel
     */
    public function __construct(Mage_Sales_Model_Order $orderModel = null)
    {
        if($orderModel){
            $this->_storeId = $orderModel->getStoreId();
            $this->_order_items = $orderModel->getAllItems();
        }
        $this->_num_order_items = count($this->_order_items);
        $this->_productMapper = new Dotdigitalgroup_Email_Model_Dynamic_Product_Mapper($this->_num_order_items);
        $this->_helper = Mage::helper('connector/recommended');
        $this->_result_limit = $this->_helper->getLimit();
    }

    /**
     * Get the recommended products
     * @return array
     */
    public function getProducts()
    {
        $mode = Mage::registry('mode');
        if($mode == 'related' || $mode == 'upsell' || $mode == 'crosssell'){

            //set the order items
            $this->setOrderItems();
        }else{
            //set products directly without looking for order items
            $this->setRecommendedProduct();
        }

        return $this->_productMapper->getProducts();
    }

    public function setOrderItems()
    {
        foreach ($this->_order_items as $item) {
            $productId = $item->getProductId();
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->load($productId);//make sure the product is loaded

            $this->setRecommendedProduct($product);
        }

        if($this->_productMapper->countProducts() < $this->_result_limit){
            $limit = $this->_result_limit - $this->_productMapper->countProducts();
            $this->_productMapper->getFallbackProducts($limit);
        }

        return $this->_productMapper->getProducts();
    }

    public function setRecommendedProduct(Mage_Catalog_Model_Product $productModel = null)
    {
        //array of products to display
        $products = array();

        $mode = Mage::registry('mode');
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
            case 'bestsellers':
                $products = $this->getBestSellersProducts();
                break;
            case 'mostviewed':
                $products = $this->getMostViewedProducts();
                break;
            case 'recentlyviewed':
                $products = $this->getRecentlyViewedProducts();
                break;
            case 'productpush':
                $products = $this->getPushProducts();
                break;
        }
        if($mode == 'related' || $mode == 'upsell' || $mode == 'crosssell'){

            $this->_productMapper->setProducts($products, $this->_result_limit);
        }


        Mage::helper('connector')->log('number of all recommended products : ' . $this->_productMapper->countProducts() . ', max : ' . $this->_result_limit, null, $this->filename);

    }
    public function getNumberOfOrderItems()
    {
        return $this->_order_items;
    }

    /**
     * Best Selling products
     * @return array
     */
    public function getBestSellersProducts()
    {
        $limit  = $this->_helper->getLimit();
        $from  = $this->_helper->getTimeFromConfig($config = 'bestsellers');
        $to = new Zend_Date();
        $to = $to->toString(Zend_Date::ISO_8601);

        $productCollection = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty($from, $to)
            ->addAttributeToSelect('*')
            ->addAttributeToSelect(array('name', 'price', 'small_image')) //edit to suit tastes
            ->setOrder('ordered_qty', 'desc') //best sellers on top
            ->setPageSize($limit);

        $productCollection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $productCollection->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);

        foreach ($productCollection as $_product) {
            $this->_productMapper->setProduct($_product);
        }

        return array();
    }

    /**
     * Most viwed products
     * @return array
     */
    public function getMostViewedProducts()
    {
        $limit = $this->_helper->getLimit();
        $from  = $this->_helper->getTimeFromConfig($config = 'mostviewed');

        $to = new Zend_Date();
        $to = $to->toString(Zend_Date::ISO_8601);
        $productCollection = Mage::getResourceModel('reports/product_collection')
            ->addViewsCount()
            ->addViewsCount($from, $to)
            ->setPageSize($limit);

        $productCollection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $productCollection->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);

        foreach ($productCollection as $_product) {
            $this->_productMapper->setProduct($_product);
        }


        return $this->_productMapper->getProducts();
    }

    /**
     * Recently viewed products
     * @return $this
     */
    public function getRecentlyViewedProducts()
    {
        $customerId = Mage::registry('customer');
        $helper = Mage::helper('connector');
        $helper->log('recentlyviewed customer  : ' . $customerId, null, $this->filename);
        if($customerId){

            $limit = $this->_helper->getLimit();

            //login customer to receive the recent products
            $session = Mage::getSingleton('customer/session');
            $session->loginById($customerId);

            /** @var Mage_Reports_Block_Product_Viewed $collection */
            $collection = Mage::getSingleton('Mage_Reports_Block_Product_Viewed');
            $items = $collection->getItemsCollection()
                ->setPageSize($limit)
            ;


            foreach ($items as $product) {
                $this->_productMapper->setProduct($product);
            }


            return $this->_productMapper->getProducts();
        }else{

            $helper->log('Get recently viewd products, customer id not found : ' . $customerId, null, $this->filename);
        }

        return null;
    }

    public function getPushProducts()
    {
        $productIds = $this->_helper->getProductPushIds();

        Mage::helper('connector')->log('proudcts push ids : ' . implode(',', $productIds), null, $this->filename);


        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $productIds))
            ->setPageSize(Mage::helper('connector/recommended')->getLimit())
        ;

        foreach ($productCollection as $_product) {
            $this->_productMapper->setProduct($_product);

        }

        return $this->_productMapper->getProducts();
    }


}