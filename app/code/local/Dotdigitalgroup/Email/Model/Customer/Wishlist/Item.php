<?php

class Dotdigitalgroup_Email_Model_Customer_Wishlist_Item
{
    protected   $name;
    protected   $sku;
    protected   $price;
    protected   $qty;
    protected   $total_value_of_product;

    public function __construct($product)
    {
        $this->setSku($product->getSku());
        $this->setName($product->getName());
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $qty
     */
    public function setQty($qty)
    {
        $this->qty = (int)$qty;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @return mixed
     */
    public function getTotalValueOfProduct()
    {
        return $this->total_value_of_product;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($product)
    {
        $this->price = $product->getFinalPrice();
        $total = $this->price * $this->qty;

        $this->total_value_of_product = number_format($total, 2);
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }
    public function expose() {

        return get_object_vars($this);

    }




}