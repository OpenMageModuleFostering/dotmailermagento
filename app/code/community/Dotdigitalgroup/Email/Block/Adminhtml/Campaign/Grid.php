<?php

class Dotdigitalgroup_Email_Block_Adminhtml_Campaign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct(){
        parent::__construct();
        $this->setId('id');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection(){

        $collection = Mage::getModel('email_connector/campaign')->getCollection();
        $this->setCollection($collection);
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        return parent::_prepareCollection();
    }



    protected function _prepareColumns(){
        $this->addColumn('id', array(
            'header'        => Mage::helper('connector')->__('Campaign ID'),
            'width'         => '20px',
            'index'         => 'id',
            'type'          => 'number',
            'truncate'      => 50,
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
        $this->addColumn('is_sent', array(
            'header'        => Mage::helper('connector')->__('Is Sent'),
            'align'         => 'center',
            'width'         => '20px',
            'index'         => 'is_sent',
            'type'          => 'number',
            'escape'        => true,
            'renderer'     => 'email_connector/adminhtml_column_renderer_imported'
        ));
        $this->addColumn('order_increment_id', array(
            'header'        => Mage::helper('connector')->__('Increment ID'),
            'align'         => 'left',
            'width'         => '50px',
            'index'         => 'order_increment_id',
            'type'          => 'number',
            'escape'        => true
        ));

        $this->addColumn('message', array(
            'header'		=> Mage::helper('connector')->__('Message'),
            'align'		=> 'left',
            'width'		=> '300px',
            'index'     => 'message',
            'type'      => 'text',
            'escape'    => true
        ));
        $this->addColumn('event_name', array(
            'header'        => Mage::helper('connector')->__('Event Name'),
            'align'         => 'left',
            'index'         => 'event_name',
            'width'		    => '100px',
            'type'          => 'string',
            'escape'        => true,
        ));
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('connector')->__('Created At'),
            'align'     => 'center',
            'width'     => '100px',
            'index'     => 'created_at',
            'type'      => 'datetime',
            'escape'    => true
        ));
        $this->addColumn('sent_at', array(
            'header'    => Mage::helper('connector')->__('Sent At'),
            'align'     => 'center',
            'width'     => '100px',
            'index'     => 'sent_at',
            'type'     => 'datetime',
            'escape'   => true
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
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('campaign');
        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('connector')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('connector')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('run', array('label'=>Mage::helper('connector')->__('Resend'),
            'url'=>$this->getUrl('*/*/massResend')));
        return $this;
    }


    public function getRowUrl($row){
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }


    public function getGridUrl(){
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}