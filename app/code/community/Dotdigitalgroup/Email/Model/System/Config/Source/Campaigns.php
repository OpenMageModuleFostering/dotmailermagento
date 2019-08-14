<?php
/**
 * Date: 15/04/2013
 * Time: 13:46
 */
 class Dotdigitalgroup_Email_Model_System_Config_Source_Campaigns
{

    // Returns the account's datafields
    public function toOptionArray()
    {
        $fields = array();
        /** @var Dotdigitalgroup_Email_Model_Connector_Api_Client $client */
        $client = Mage::getModel('connector/connector_api_client');

        $websiteName = Mage::app()->getRequest()->getParam('website');
        if(! empty($websiteName)){
            $website = Mage::getModel('core/website')->getCollection()
                ->addFieldToFilter('code', $websiteName)->getFirstItem();

            $client->setApiUsername(Mage::helper('connector')->getApiUsername($website));
            $client->setApiPassword(Mage::helper('connector')->getApiPassword($website));
        }
        $campaigns = $client->getCampaigns();

        foreach ($campaigns as $one){
            if(isset($one->id))
                $fields[] = array('value' => $one->id, 'label' => $one->name);
        }

        return $fields;
    }

}