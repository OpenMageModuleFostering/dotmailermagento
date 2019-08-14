<?php

class Dotdigitalgroup_Email_Model_Connector_Datafield
{
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @var string
	 */
	public $visibility;
	/**
	 * @var string
	 */
	public $defaultValue;
	/**
	 * Contact datafields.
	 * @var array
	 */
	public $datafields = array();

    /**
     * Default datafields
     * @return array
     */
    protected $_defaultDataFields = array(
        array(
            'name' => 'CUSTOMER_ID',
            'type' => 'string',
            'visibility' => 'private',
        ),array(
            'name' => 'LAST_ORDER_ID',
            'type' => 'numeric',
            'visibility' => 'private',
        ),array(
            'name' => 'ORDER_INCREMENT_ID',
            'type' => 'numeric',
            'visibility' => 'private',
        )
    );

	/**
	 * Contact default datafields.
	 *
	 * @var array
	 */
	private $_contactDatafields = array(
        'customer_id' => array(
            'name' => 'CUSTOMER_ID',
            'type' => 'numeric',
            'visibility' => 'public',
        ),
        'firstname' => array(
            'name' => 'FIRSTNAME',
            'type' => 'string'
        ),
        'lastname' => array(
            'name' => 'LASTNAME',
            'type' => 'string'
        ),
        'gender' => array(
            'name' => 'GENDER',
            'type' => 'string'
        ),
        'dob' => array(
            'name' => 'DOB',
            'type' => 'date',
            'visibility' => 'public',
        ),
        'title' => array(
            'name' => 'TITLE',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'website_name' => array(
            'name' => 'WEBSITE_NAME',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'store_name' => array(
            'name' => 'STORE_NAME',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'created_at' => array(
            'name' => 'ACCOUNT_CREATED_DATE',
            'type' => 'date',
            'visibility' => 'public'
        ),
        'last_logged_date' => array(
            'name' => 'LAST_LOGGEDIN_DATE',
            'type' => 'date',
            'visibility' => 'public'
        ),
        'customer_group' => array(
            'name' => 'CUSTOMER_GROUP',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'billing_address_1' => array(
            'name' => 'BILLING_ADDRESS_1',
            'type' => 'string',
            'visibility' => 'private',
            'defaultValue' => ''
        ),
        'billing_address_2' => array(
            'name' => 'BILLING_ADDRESS_2',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'billing_state' => array(
            'name' => 'BILLING_STATE',
            'type' => 'string',
            'visibility' => 'public'
        ),
        'billing_city' => array(
            'name' => 'BILLING_CITY',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'billing_country' => array(
            'name' => 'BILLING_COUNTRY',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'billing_postcode' => array(
            'name' => 'BILLING_POSTCODE',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'billing_telephone' => array(
            'name' => 'BILLING_TELEPHONE',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'delivery_address_1' => array(
            'name' => 'DELIVERY_ADDRESS_1',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'delivery_address_2' => array(
            'name' => 'DELIVERY_ADDRESS_2',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'delivery_state' => array(
            'name' => 'DELIVERY_STATE',
            'type' => 'string',
            'visibility' => 'public'
        ),
        'delivery_city' => array(
            'name' => 'DELIVERY_CITY',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'delivery_country' => array(
            'name' => 'DELIVERY_COUNTRY',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'delivery_postcode' => array(
            'name' => 'DELIVERY_POSTCODE',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'delivery_telephone' => array(
            'name' => 'DELIVERY_TELEPHONE',
            'type' => 'string',
            'visibility' => 'public',
        ),
        'number_of_orders' => array(
            'name' => 'NUMBER_OF_ORDERS',
            'type' => 'numeric',
            'visibility' => 'public',
        ),
        'total_spend' => array(
            'name' => 'TOTAL_SPEND',
            'type' => 'numeric',
            'visibility' => 'public',
        ),
        'average_order_value' => array(
            'name' => 'AVERAGE_ORDER_VALUE',
            'type' => 'numeric',
            'visibility' => 'public',
        ),
        'last_order_date' => array(
            'name' => 'LAST_ORDER_DATE',
            'type' => 'date',
            'visibility' => 'public',
        ),
        'last_order_id' => array(
            'name' => 'LAST_ORDER_ID',
            'type' => 'numeric',
            'visibility' => 'private',
        )
    );

	/**
	 * transactional data default datafields.
	 *
	 * @var array
	 */
	private $_transactionalDefaultDatafields = array(
        array(
            'name' => 'CUSTOMER_ID',
            'type' => 'string',
            'visibility' => 'private',
        ),array(
            'name' => 'LAST_ORDER_ID',
            'type' => 'numeric',
            'visibility' => 'private',
        ),array(
            'name' => 'ORDER_INCREMENT_ID',
            'type' => 'numeric',
            'visibility' => 'private',
        ),
        array(
            'name' => 'WEBSITE_NAME',
            'type' => 'string',
            'visibility' => 'private',
        ),
        array(
            'name' => 'STORE_NAME',
            'type' => 'string',
            'visibility' => 'private',
        ),
        array(
            'name' => 'LAST_ORDER_DATE',
            'type' => 'date',
            'visibility' => 'private',
        )
    );

    /**
     * @param array $contactDatafields
     */
    public function setContactDatafields($contactDatafields)
    {
        $this->_contactDatafields = $contactDatafields;
    }

    /**
     * @return array
     */
    public function getContactDatafields()
    {
        return $this->_contactDatafields;
    }

    /**
     * @param array $transactionalDefaultDatafields
     */
    public function setTransactionalDefaultDatafields($transactionalDefaultDatafields)
    {
        $this->_transactionalDefaultDatafields = $transactionalDefaultDatafields;
    }

    /**
     * @return array
     */
    public function getTransactionalDefaultDatafields()
    {
        return $this->_transactionalDefaultDatafields;
    }

    /**
     * @param mixed $defaultDataFields
     */
    public function setDefaultDataFields($defaultDataFields)
    {
        $this->_defaultDataFields = $defaultDataFields;
    }

    /**
     * @return mixed
     */
    public function getDefaultDataFields()
    {
        return $this->_defaultDataFields;
    }

	/**
	 * set a single datafield.
	 *
	 * @param $name
	 * @param $value
	 * @param string $type
	 * @param string $visibility
	 *
	 * @return array
	 */
	public function setDatafield($name, $value, $type = 'string', $visibility = 'public')
    {
        $this->datafields[] = array(
            'name' => $name,
            'value' => $value,
            'type' => $type,
            'visibility' => $visibility
        );
        return $this->datafields;
    }

}