<?php
/**
 * Date: 15/04/2013
 * Time: 13:46
 */
 class Dotdigitalgroup_Email_Model_System_Config_Source_Datafields extends Dotdigitalgroup_Email_Model_Api_Rest
{

    // Returns the account's datafields
    public function toOptionArray()
    {
        $fields = array();
        //load the configuration for website select in admin
        $websiteName = Mage::app()->getRequest()->getParam('website');
        if(! empty($websiteName)){
            $websites = Mage::getModel('core/website')->getCollection()
                ->addFieldToFilter('code', $websiteName);
            $website = $websites->getFirstItem();
            $this->_api_user = $website->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME);
            $this->_api_password = $website->getConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD);
        }

        // Add a "Do Not Map" Option
        $fields[] = array('value' => 0, 'label' => 'Do Not Map');

        $datafileds = $this->postDataFields();
        foreach ($datafileds as $datafield) {
            if(isset($datafield->name))
                $fields[] = array('value' => $datafield->name, 'label' => $datafield->name);
        }

        return $fields;
    }

}