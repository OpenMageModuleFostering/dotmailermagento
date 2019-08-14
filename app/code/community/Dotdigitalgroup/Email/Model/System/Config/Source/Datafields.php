<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_Datafields
{
    // Returns the account's datafields
    public function toOptionArray()
    {
        $helper = Mage::helper('connector');
        $fields = array();

        $website = Mage::app()->getRequest()->getParam('website');
        $client = Mage::getModel('connector/connector_api_client');

        $client->setApiUsername($helper->getApiUsername($website));
        $client->setApiPassword($helper->getApiPassword($website));


        /**
         * api get all datafields
         */
        $datafields = $client->getDataFields();
        // Add a "Do Not Map" Option
        $fields[] = array('value' => 0, 'label' => 'Do Not Map');

        if(isset($datafields->message)){
            $fields[] = array('value' => 0, 'label' => $datafields->message);
        }


        foreach ($datafields as $datafield) {
            if(isset($datafield->name))
                $fields[] = array('value' => $datafield->name, 'label' => $datafield->name);
        }

        return $fields;
    }
}