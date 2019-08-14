<?php

 class Dotdigitalgroup_Email_Model_System_Config_Source_Addressbooks extends Dotdigitalgroup_Email_Model_Api_Rest
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
        $addressBooks = $this->postAddressBooks();

        //set up fields with book id and label
        foreach ($addressBooks as $book){

            if(isset($book->id))
                $fields[] = array('value' => $book->id, 'label' => $book->name);
        }

        return $fields;
    }

}