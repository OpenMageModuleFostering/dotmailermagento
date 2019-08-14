<?php

class Dotdigitalgroup_Email_Block_Adminhtml_Dashboard extends  Mage_Adminhtml_Block_Dashboard_Bar

{
    /**
	 * Set the template.
	 */
    public function __construct()
    {
        parent::_construct();

        $this->setTemplate('connector/dashboard/accountbar.phtml');
    }

    /**
	 * Prepare the layout.
	 *
	 * @return Mage_Core_Block_Abstract|void
	 * @throws Exception
	 */
    protected function _prepareLayout()
    {
        $website = 0;
        if ($store = $this->getRequest()->getParam('store')) {
            $website = Mage::app()->getStore($store)->getWebsite();
        } elseif ($this->getRequest()->getParam('website')) {
            $website = $this->getRequest()->getParam('website');
        }
        $apiUsername = Mage::helper('connector')->getApiUsername($website);
        $apiPassword = Mage::helper('connector')->getApiPassword($website);
        $data = Mage::getModel('email_connector/apiconnector_client')
            ->setApiUsername($apiUsername)
            ->setApiPassword($apiPassword)
            ->getAccountInfo();
        foreach ($data->properties as $one) {
            $this->addTotal($this->__($one->name), $one->value, true);
        }
    }

}
