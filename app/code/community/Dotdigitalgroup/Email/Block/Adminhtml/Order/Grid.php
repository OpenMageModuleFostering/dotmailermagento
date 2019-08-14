<?php

class Dotdigitalgroup_Email_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('email_order_id');
        $this->setId('email_order_id');
        $this->setDefaultDir('asc');
    }

    /**
	 * Collection class;
	 * @return string
	 */
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'email_connector/order_collection';
    }

    /**
	 * Prepare the grid collection.
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
	 * Prepare the grid collumns.
	 * @return $this
	 * @throws Exception
	 */
    protected function _prepareColumns()
    {
        $this->addColumn('order_id', array(
            'header'        => Mage::helper('connector')->__('Order ID'),
            'align'         => 'left',
            'width'         => '50px',
            'index'         => 'order_id',
            'type'          => 'number',
            'escape'        => true
        ))->addColumn('store_id', array(
            'header'        => Mage::helper('connector')->__('Store ID'),
            'width'         => '50px',
            'index'         => 'store_id',
            'type'          => 'number',
            'escape'        => true,
        ))->addColumn('email_imported', array(
            'header'        => Mage::helper('connector')->__('Email Imported'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'email_imported',
            'type'          => 'options',
            'escape'        => true,
            'renderer'		=> 'email_connector/adminhtml_column_renderer_imported',
            'options'       => Mage::getModel('email_connector/adminhtml_source_contact_imported')->getOptions(),
            'filter_condition_callback' => array($this, 'filterCallbackContact')
        ))->addColumn('updated_at', array(
            'header'        => Mage::helper('connector')->__('Updated At'),
            'width'         => '50px',
            'align'         => 'center',
            'index'         => 'updated_at',
            'type'          => 'datetime',
            'escape'        => true,
        ));

        return parent::_prepareColumns();
    }

    /**
	 * Callback action for the imported subscribers/contacts.
	 *
	 * @param $collection
	 * @param $column
	 */
    public function filterCallbackContact($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == 'null') {
            $collection->addFieldToFilter($field, array('null' => true));
        } else {
            $collection->addFieldToFilter($field, array('notnull' => true));
        }
    }
}