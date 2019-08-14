<?php

 class Dotdigitalgroup_Email_Model_System_Config_Source_Addressbooks
{
     // Returns the account's datafields
    public function toOptionArray()
    {
        $fields = array();

        $website = Mage::app()->getRequest()->getParam('website');
        $helper = Mage::helper('connector');
        $client = Mage::getModel('connector/connector_api_client');


        $client->setApiUsername($helper->getApiUsername($website));
        $client->setApiPassword($helper->getApiPassword($website));

        // Add a "Do Not Map" Option
        $fields[] = array('value' => 0, 'label' => 'Do Not Map');

        // api all address books
        $addressBooks = $client->getAddressBooks();

        if(isset($addressBooks->message)){
            $fields[] = array('value' => 0, 'label' => $addressBooks->message);
        }

        //set up fields with book id and label
        foreach ($addressBooks as $book){
            if(isset($book->id))
                $fields[] = array('value' => $book->id, 'label' => $book->name);
        }

        return $fields;
    }

}