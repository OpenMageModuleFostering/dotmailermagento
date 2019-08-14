<?php

class Dotdigitalgroup_Email_Block_Basket extends Mage_Core_Block_Template
{
    protected $_quote;

    /**
	 * Basket itmes.
	 *
	 * @return mixed
	 * @throws Exception
	 * @throws Mage_Core_Exception
	 */
    public function getBasketItems()
    {
        $params = $this->getRequest()->getParams();

        if (!isset($params['email']) && !isset($params['code']))
            exit();
        Mage::helper('connector')->auth($params['code']);

        $email = $params['email'];

        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($email);

        if (! $customer->getId()) {
            Mage::helper('connector')->log('Lost basket, customer not found : ' . $email);
            exit();
        }
        //last active  guest  basket
        $quoteModel = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('items_count', array('gt' => 0))
            ->addFieldToFilter('customer_email', $email)
            ->setOrder('updated_at', 'DESC')
            ->setPageSize(1);

        $quoteModel = $quoteModel->getFirstItem();
        $this->_quote = $quoteModel;

	    //Start environment emulation of the specified store
	    $storeId = $quoteModel->getStoreId();
	    $appEmulation = Mage::getSingleton('core/app_emulation');
	    $appEmulation->startEnvironmentEmulation($storeId);

        return $quoteModel->getAllItems();
    }

    /**
	 * Grand total.
	 *
	 * @return mixed
	 */
    public function getGrandTotal()
    {
        return $this->_quote->getGrandTotal();

    }
}