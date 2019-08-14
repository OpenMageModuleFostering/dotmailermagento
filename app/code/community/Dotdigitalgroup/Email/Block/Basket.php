<?php

class Dotdigitalgroup_Email_Block_Basket extends Mage_Core_Block_Template
{
    protected $_quote;

    public function getBasketItems()
    {
        $params = $this->getRequest()->getParams();

        if(!isset($params['email']) && !isset($params['code']))
            exit();
        Mage::helper('connector')->auth($params['code']);

        $email = $params['email'];

        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($email);

        //last active  guest  basket
        $quoteModel = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('items_count', array('gt' => 0))
            ->addFieldToFilter('customer_email', $email)
            ->setOrder('updated_at' , 'DESC')
            ->setPageSize(1)
        ;

        $quoteModel = $quoteModel->getFirstItem();
        $this->_quote = $quoteModel;

        $store_id = $quoteModel->getStoreId();
        Mage::app()->setCurrentStore($store_id);

        return $quoteModel->getAllItems();;
    }

    public function getGrandTotal()
    {
        return $this->_quote->getGrandTotal();

    }
}