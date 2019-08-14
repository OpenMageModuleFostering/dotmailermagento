<?php

class Dotdigitalgroup_Email_Model_Customer_Wishlist
{
    public  $id;
    public  $customer_id;
    public  $connector_id;

    public $items = array();

    protected $total_wishlist_value;

    public function __construct(Mage_Customer_Model_Customer $customer){

        $this->setCustomerId($customer->getId());
    }

    /**
     * @param mixed $connector_id
     */
    public function setConnectorId($connector_id)
    {
        $this->connector_id = $connector_id;
    }

    /**
     * @return mixed
     */
    public function getConnectorId()
    {
        return $this->connector_id;
    }


    /**
     * @param mixed $customer_id
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
    }


    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setItem($item)
    {

        $this->items[] = $item->expose();

        $this->total_wishlist_value += $item->getTotalValueOfProduct();
    }

    public function expose() {

        return get_object_vars($this);

    }



}