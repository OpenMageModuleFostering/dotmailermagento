<?php

class Dotdigitalgroup_Email_Model_Customer_Wishlist
{
	/**
	 * @var int
	 */
	public  $id;
	/**
	 * @var int
	 */
	public  $customer_id;
	/**
	 * @var string
	 */
	public $email;

	/**
	 * wishlist items.
	 * @var array
	 */
	public $items = array();

	/**
	 * @var float
	 */
	protected $total_wishlist_value;

	/**
	 * constructor.
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 */
	public function __construct(Mage_Customer_Model_Customer $customer)
    {

        $this->setCustomerId($customer->getId());
        $this->email = $customer->getEmail();
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

	/**
	 * set wishlist item.
	 *
	 * @param $item
	 */
	public function setItem($item)
    {
        $this->items[] = $item->expose();

        $this->total_wishlist_value += $item->getTotalValueOfProduct();
    }

	/**
	 * @return array
	 */
	public function expose()
	{
        return get_object_vars($this);
    }
}