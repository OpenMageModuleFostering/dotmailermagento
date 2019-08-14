<?php

class Dotdigitalgroup_Email_Model_Connector_Wishlist
{
    protected  $id;
    protected  $customer_id;
    protected  $connector_id;

    public $name;

    public $items = array();

    protected $total_wishlist_value;

    public function __construct($customer){

        $this->setConnectorId($customer->getDotmailerContactId());
        $this->setCustomerId($customer->id);
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
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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