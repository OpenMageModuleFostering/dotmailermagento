<?php

class Dotdigitalgroup_Email_Helper_Recommended extends Mage_Core_Helper_Abstract
{
    const XML_PATH_RELATED_PRODUCTS_TYPE        = 'dynamic_content/products/related_display_type';
    const XML_PATH_UPSELL_PRODUCTS_TYPE         = 'dynamic_content/products/upsell_display_type';
    const XML_PATH_CROSSSELL_PRODUCTS_TYPE      = 'dynamic_content/products/crosssell_display_type';
    const XML_PATH_BESTSELLER_PRODUCT_TYPE      = 'dynamic_content/products/best_display_type';
    const XML_PATH_MOSTVIEWED_PRODUCT_TYPE      = 'dynamic_content/products/most_viewed_display_type';
    const XML_PATH_RECENTLYVIEWED_PRODUCT_TYPE  = 'dynamic_content/products/recently_viewed_display_type';
    const XML_PATH_PRODUCTPUSH_TYPE             = 'dynamic_content/manual_product_search/display_type';


    const XML_PATH_RELATED_PRODUCTS_ITEMS       = 'dynamic_content/products/related_items_to_display';
    const XML_PATH_UPSELL_PRODUCTS_ITEMS        = 'dynamic_content/products/upsell_items_to_display';
    const XML_PATH_CROSSSELL_PRODUCTS_ITEMS     = 'dynamic_content/products/crosssell_items_to_display';
    const XML_PATH_BESTSELLER_PRODUCT_ITEMS     = 'dynamic_content/products/best_items_to_display';
    const XML_PATH_MOSTVIEWED_PRODUCT_ITEMS     = 'dynamic_content/products/most_viewed_items_to_display';
    const XML_PATH_RECENTLYVIEWED_PRODUCT_ITEMS = 'dynamic_content/products/recently_viewed_items_to_display';
    const XML_PATH_PRODUCTPUSH_DISPLAY_ITEMS    = 'dynamic_content/manual_product_search/items_to_display';


    const XML_PATH_BESTSELLER_TIME_PERIOD   = 'dynamic_content/products/best_time_period';
    const XML_PATH_MOSTVIEWED_TIME_PERIOD   = 'dynamic_content/products/most_viewed_time_period';

    const XML_PATH_FALLBACK_PRODUCTS_ITEMS  = 'dynamic_content/fallback_products/product_list';

    const XML_PATH_PRODUCTPUSH_ITEMS       = 'dynamic_content/manual_product_search/products_push_list';

    public $periods = array('week', 'month', 'year');


    /**
     * product recommendation type
     * @var string
     */


    /**
     * Dispay mode
     * @return mixed|string grid:list
     */
    public function getMode()
    {
        $mode = Mage::app()->getRequest()->getParam('mode');
        $type = '';
        if($mode){
            switch($mode){
                case 'related':
                    $type = $this->getRelatedProductsType();
                    break;
                case 'upsell':
                    $type = $this->getUpsellProductsType();
                    break;
                case 'crosssell':
                    $type = $this->getCrosssellProductsType();
                    break;
                case 'bestsellers':
                    $type = $this->getBestSellerProductsType();
                    break;
                case 'mostviewed':
                    $type = $this->getMostViewedProductsType();
                    break;
                case 'recentlyviewed':
                    $type  = $this->getRecentlyviewedProductsType();
                    break;
                case 'productpush':
                    $type = $this->getProductpushProductsType();
            }
        }

        return $type;
    }

    public function getRelatedProductsType($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_RELATED_PRODUCTS_TYPE, $storeId);
    }

    public function getUpsellProductsType($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_UPSELL_PRODUCTS_TYPE, $storeId);

    }

    public function getCrosssellProductsType($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_CROSSSELL_PRODUCTS_TYPE, $storeId);
    }

    public function getBestSellerProductsType($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_BESTSELLER_PRODUCT_TYPE, $storeId);
    }

    public function getMostViewedProductsType($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_MOSTVIEWED_PRODUCT_TYPE, $storeId);
    }

    public function getRecentlyviewedProductsType($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_RECENTLYVIEWED_PRODUCT_TYPE, $storeId);
    }

    public function getProductpushProductsType($storeId = 0)
    {
        return Mage::getStoreConfig(self::XML_PATH_PRODUCTPUSH_TYPE, $storeId);
    }



    /**
     * Limit of products displayed
         * @return int|mixed
     */
    public function getLimit()
    {
        $mode = Mage::registry('mode');
        $limit = 0;
        if($mode){
            switch($mode){
                case 'related':
                    $limit = Mage::getStoreConfig(self::XML_PATH_RELATED_PRODUCTS_ITEMS);
                    break;
                case 'upsell':
                    $limit = Mage::getStoreConfig(self::XML_PATH_UPSELL_PRODUCTS_ITEMS);
                    break;
                case 'crosssell':
                    $limit = Mage::getStoreConfig(self::XML_PATH_CROSSSELL_PRODUCTS_ITEMS);
                    break;
                case 'bestsellers':
                    $limit = Mage::getStoreConfig(self::XML_PATH_BESTSELLER_PRODUCT_ITEMS);
                    break;
                case 'mostviewed':
                    $limit = Mage::getStoreConfig(self::XML_PATH_MOSTVIEWED_PRODUCT_ITEMS);
                    break;
                case 'recentlyviewed':
                    $limit = Mage::getStoreConfig(self::XML_PATH_RECENTLYVIEWED_PRODUCT_ITEMS);
                    break;
                case 'productpush':
                    $limit = Mage::getStoreConfig(self::XML_PATH_PRODUCTPUSH_DISPLAY_ITEMS);
            }
        }

        return $limit;
    }

    public function getFallbackIds(){
        $fallbackIds = Mage::getStoreConfig(self::XML_PATH_FALLBACK_PRODUCTS_ITEMS);
        if($fallbackIds)
            return explode(',', Mage::getStoreConfig(self::XML_PATH_FALLBACK_PRODUCTS_ITEMS));
        return array();
    }

    public function getTimeFromConfig($config)
    {
        $now = new Zend_Date();
        $period = '';
        if($config == 'mostviewed')
            $period = Mage::getStoreConfig(self::XML_PATH_MOSTVIEWED_TIME_PERIOD);
        elseif($config == 'bestsellers')
            $period = Mage::getStoreConfig(self::XML_PATH_BESTSELLER_TIME_PERIOD);
        elseif($config == 'recentlyviewed')
            $period = Mage::getStoreConfig(self::XML_PATH_MOSTVIEWED_TIME_PERIOD);

        if($period == 'week'){
            $sub = Zend_Date::WEEK;
        }elseif($period == 'month'){
            $sub = Zend_Date::MONTH;
        }elseif($period == 'year'){
            $sub = Zend_Date::YEAR;
        }

        if(isset($sub)){
            $period = $now->sub(1, $sub);

            return $period->tostring(Zend_Date::ISO_8601);
        }
    }

    public function getProductPushIds($storeId = 0)
    {
        $productIds = Mage::getStoreConfig(self::XML_PATH_PRODUCTPUSH_ITEMS, $storeId);

        return explode(',', $productIds);

    }


}