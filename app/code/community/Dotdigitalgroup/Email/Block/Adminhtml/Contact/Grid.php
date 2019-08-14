<?php

class Dotdigitalgroup_Email_Block_Adminhtml_Contact_Grid extends Mage_Adminhtml_Block_Widget_Grid
{


    public function __construct(){
        parent::__construct();
        $this->setId('email_contact_id');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection(){

        $collection = Mage::getModel('email_connector/contact')->getCollection();
        $this->setCollection($collection);
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        return parent::_prepareCollection();
    }



    protected function _prepareColumns(){

        $this->addColumn('email_contact_id', array(
            'header'        => Mage::helper('connector')->__('Email Contact ID'),
            'width'         => '20px',
            'index'         => 'email_contact_id',
            'type'          => 'number',
            'escape'        => true,
        ));

        $this->addColumn('email', array(
            'header'        => Mage::helper('connector')->__('Email'),
            'align'         => 'left',
            'width'         => '50px',
            'index'         => 'email',
            'type'          => 'text',
            'escape'        => true
        ));
        $this->addColumn('is_guest', array(
            'header'        => Mage::helper('connector')->__('Is Guest'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'is_guest',
            'type'          => 'string',
            'escape'        => true,
        ));

        $this->addColumn('customer_id', array(
            'header'        => Mage::helper('connector')->__('Customer ID'),
            'align'         => 'left',
            'width'         => '20px',
            'index'         => 'customer_id',
            'type'          => 'number',
            'escape'        => true
        ));
        $this->addColumn('is_subscrier', array(
            'header'        => Mage::helper('connector')->__('Is Subscriber'),
            'width'         => '50px',
            'align'         => 'right',
            'index'         => 'is_subscriber',
            'type'          => 'string',
            'escape'        => true,
        ));

        $this->addColumn('subscribe_status', array(
            'header'        => Mage::helper('connector')->__('Subscriber Status'),
            'align'         => 'center',
            'width'         => '50px',
            'index'         => 'subscriber_status',
            'type'          => 'string',
            'escape'        => true,
            'renderer'     => 'email_connector/adminhtml_column_renderer_status'

        ));
        $this->addColumn('email_imported', array(
            'header'        => Mage::helper('connector')->__('Email Imported'),
            'width'         => '20px',
            'align'         => 'center',
            'index'         => 'email_imported',
            'type'          => 'string',
            'escape'        => true,
            'renderer'     => 'email_connector/adminhtml_column_renderer_imported'
        ));

        $this->addColumn('subscriber_imported', array(
            'header'        => Mage::helper('connector')->__('Subscriber Imported'),
            'width'         => '20px',
            'align'         => 'center',
            'index'         => 'subscriber_imported',
            'type'          => 'string',
            'escape'        => true,
            'renderer'     => 'email_connector/adminhtml_column_renderer_imported'
        ));
        $this->addColumn('suppressed', array(
            'header'        => Mage::helper('connector')->__('Suppressed'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'suppressed',
            'type'          => 'string',
            'escape'        => true
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('connector')->__('CSV'));
        //$this->addExportType('*/*/exportExcel', Mage::helper('connector')->__('Excel'));
        //$this->addExportType('*/*/exportXml', Mage::helper('connector')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }


    protected function _prepareMassaction(){
        $this->setMassactionIdField('email_contact_id');
        $this->getMassactionBlock()->setFormFieldName('contact');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('connector')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('connector')->__('Are you sure?')
        ));
        return $this;
    }


    public function getRowUrl($row){
        //return $this->getUrl('*/*/edit', array('id' => $row->getEmailContactId()));
    }

    public function getGridUrl(){
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}