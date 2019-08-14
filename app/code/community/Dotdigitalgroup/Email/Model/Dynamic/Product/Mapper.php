<?php

class Dotdigitalgroup_Email_Model_Dynamic_Product_Mapper extends Mage_Core_Model_Abstract
{
    protected $_max_per_child;

    protected $_num_order_items;

    protected $_helper;

    public $_products = array();

    public $filename = 'mapper.log';

    public function __construct($num_order_items)
    {
        $this->_helper = Mage::helper('connector');
        $items_to_display = Mage::helper('connector/recommended')->getLimit();
        if($num_order_items < $items_to_display)
            $this->_max_per_child = number_format($items_to_display / $num_order_items);
        $this->_helper->log('max items per child : ' . $this->_max_per_child . ', items to display : ' . $items_to_display . ', number of order items : ' . $num_order_items, null, $this->filename);

    }

    /**
     * Set the products with parent limit
     * @param $products
     */
    public function setProducts($products)
    {
        $count = 0;
        foreach ($products as $product) {
            if($count < $this->_max_per_child)
                $this->setProduct($product);
            $count++;
        }

    }
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $productId = $product->getEntityId();

        if(!isset($this->_products[$productId])){
            $this->_products[$productId] = $product;
            return true;
        }
        return  false;
    }

    public function countProducts()
    {
        return count($this->_products);
    }

    public function getProducts()
    {
        return $this->_products;
    }

    public function getFallbackProducts($limit)
    {
        $count = 0;
        $fallbackIds = Mage::helper('connector/recommended')->getFallbackIds();
        $this->_helper->log('fallback products from helper : '. count($fallbackIds), null, $this->filename);

        if(!empty($fallbackIds)){
            foreach ($fallbackIds as $id) {
                /** @var Mage_Catalog_Model_Product $product */
                $product = Mage::getModel('catalog/product')->load($id);

                if($product->getEntityId()){

                    $result = $this->setProduct($product);
                    if($result)
                        $count++;

                }

                $this->_helper->log('result for products ' . $this->countProducts() . ', count : ' . $count . ', limit : ' . $limit, null, $this->filename);

                if($count == $limit){

                    break;
                }
            }
        }

        return;
    }
}