<?php

class Dotdigitalgroup_Email_Model_Adminhtml_Source_Transactional_Campaigns
{

	/**
	 * Returns the account's datafields.
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

        $savedCampaigns = Mage::registry('savedcampigns');

        if ($savedCampaigns) {
            $campaigns = $savedCampaigns;
        } else {
            $campaigns = $client->getCampaigns();
            Mage::unregister('savedcampigns');
            Mage::register('savedcampigns', $campaigns);
        }

	    $fields[] = array('value' => '0', 'label' => Mage::helper('connector')->__('-- Use system default --'));

	    if (is_array($fields)) {
		    foreach ( $campaigns as $one ) {
			    if ( isset( $one->id ) )
				    $fields[] = array( 'value' => $one->id, 'label' => Mage::helper( 'connector' )->__( addslashes($one->name)) );
		    }
	    }

        return $fields;
    }

}