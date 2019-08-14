<?php

class Dotdigitalgroup_Email_Model_Adminhtml_Source_Transactional_Fromaddress
{

	/**
	 * Returns all custom from addresses.
	 *
	 * @return array
	 * @throws Mage_Core_Exception
	 */
	public function toOptionArray()
    {
        $fields = array();
        $client = Mage::getModel('email_connector/apiconnector_client');

        $websiteName = Mage::app()->getRequest()->getParam('website', false);
        if ($websiteName) {
            $website = Mage::app()->getWebsite($websiteName);
        } else {
            $website = 0;
        }
        $client->setApiUsername(Mage::helper('connector/transactional')->getApiUsername($website));
        $client->setApiPassword(Mage::helper('connector/transactional')->getApiPassword($website));

        $savedFromAddressList = Mage::registry('savedFromAddressList');

        if ($savedFromAddressList) {
            $fromAddressList = $savedFromAddressList;
        } else {
            $fromAddressList = $client->getCampaignFromAddressList();
            Mage::unregister('savedFromAddressList');
            Mage::register('savedFromAddressList', $fromAddressList);
        }
        $fields[] = array('value' => '0', 'label' => Mage::helper('connector')->__('-- Please select --'));
        foreach ($fromAddressList as $one) {
            if(isset($one->id))
                $fields[] = array('value' => $one->id, 'label' => Mage::helper('connector')->__($one->email));
        }
        return $fields;
    }

}