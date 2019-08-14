<?php

class Dotdigitalgroup_Email_Block_Customer extends Mage_Core_Block_Template
{

    /**
	 * Prepare layout, set template.
	 *
	 * @return Mage_Core_Block_Abstract|void
	 */
    protected function _prepareLayout()
    {
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->setTemplate('page/blank.phtml');
        }
    }

    /**
	 * Recommended dynamic products.
	 *
	 * @return array
	 * @throws Exception
	 */
    public function getRecommendedProducts()
    {
        $customerId = $this->getRequest()->getParam('customer_id', false);
        $mode  = $this->getRequest()->getParam('mode', false);
        if ($mode && $customerId) {
            $customerModel = Mage::getModel('customer/customer')->load($customerId);
            Mage::app()->setCurrentStore($customerModel->getStoreId());
            if ($customerModel->getId()) {
                //order products
                $dynamicReport = Mage::getModel('email_connector/dynamic_report');
                $dynamicReport->setMode($mode)
                    ->setCustomer($customerModel);
            } else {
                Mage::helper('connector')->log('ERROR Dynamic content : Customer not found '. $customerId);
                return array();
            }
        } else {
            //load the reports from dynamic
            $dynamicReport = Mage::getModel('email_connector/dynamic_report');
            $dynamicReport->setMode($mode);
        }
        $productsToDisplay = $dynamicReport->getProducts();

        return $productsToDisplay;
    }

    public function getCustomer()
    {
        $message = new Varien_Object();
        $customerId = Mage::app()->getRequest()->getParam('customer_id', false);
        if ($customerId) {
            $customerModel = Mage::getModel('customer/customer')->load($customerId);
            if (! $customerModel->getId()) {
                Mage::helper('connector')->log('Error: New customer, no custoemr found : ' . $customerId);
                return $message->setError('No customer found : ' . $customerId);
            }
            return $customerModel;

        } else {
            return $message->setError('No customer id in param request : ' . $customerId);
        }
    }

    /**
	 * Display type mode.
	 * @return mixed|string
	 */
    public function getDisplayType()
    {
        return Mage::helper('connector/recommended')->getDisplayType();
    }

    /**
	 * @return Mage_Core_Model_Store
	 */
    public function getStore()
    {
        $customerId = Mage::app()->getRequest()->getParam('customer_id', false);
        return Mage::app()->getStore(Mage::getModel('customer/customer')->load($customerId)->getStoreId());
    }

    public function getConfirmation()
    {
        $customerId = Mage::app()->getRequest()->getParam('customer_id', false);
        $customerModel = Mage::getModel('customer/customer')->load($customerId);

        if ($confirmation = $customerModel->getConfirmation()) {
            return $confirmation;
        }
        Mage::helper('connector')->log('Customer already confirmed the account');
    }

}