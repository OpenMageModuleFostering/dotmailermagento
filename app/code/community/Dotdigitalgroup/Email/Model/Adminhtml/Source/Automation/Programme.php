<?php

class Dotdigitalgroup_Email_Model_Adminhtml_Source_Automation_Programme
{

	public function toOptionArray()
	{
		$fields = array();
		$websiteName = Mage::app()->getRequest()->getParam('website', false);
		//admin
		$website = 0;
		if ($websiteName) {
			$website = Mage::app()->getWebsite($websiteName);
		}
		$client = Mage::helper('connector')->getWebsiteApiClient($website);


		/**
		$savedCampaigns = Mage::registry('savedcampigns');

		if ($savedCampaigns) {
			$campaigns = $savedCampaigns;
		} else {
			$campaigns = $client->getCampaigns();
			Mage::register('savedcampigns', $campaigns);
		}
		 **/

		$programmes = $client->GetPrograms();
		$fields[] = array('value' => '0', 'label' => Mage::helper('connector')->__('-- Disabled --'));

		foreach ($programmes as $one) {
			if(isset($one->id))
				$fields[] = array('value' => $one->id, 'label' => Mage::helper('connector')->__($one->name));
		}

		return $fields;
	}

}