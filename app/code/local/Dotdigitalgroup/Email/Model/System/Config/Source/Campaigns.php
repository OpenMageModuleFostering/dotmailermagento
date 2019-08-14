<?php
/**
 * Date: 15/04/2013
 * Time: 13:46
 */
 class Dotdigitalgroup_Email_Model_System_Config_Source_Campaigns extends Dotdigitalgroup_Email_Model_Api_Rest
{

    // Returns the account's datafields
    public function toOptionArray()
    {
        $fields = array();
        $websiteName = Mage::app()->getRequest()->getParam('website');
        if(! empty($websiteName)){
            $websites = Mage::getModel('core/website')->getCollection()
                ->addFieldToFilter('code', $websiteName);
            $website = $websites->getFirstItem();
            $this->_api_user = $website->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME);
            $this->_api_password = $website->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD);
        }
        $campaigns = $this->postCampaigns();
        foreach ($campaigns as $one){
            if(isset($one->id))
                $fields[] = array('value' => $one->id, 'label' => $one->name);
        }

        return $fields;
    }

}