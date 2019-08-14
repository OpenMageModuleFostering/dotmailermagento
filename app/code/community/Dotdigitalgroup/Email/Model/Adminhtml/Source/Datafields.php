<?php

class Dotdigitalgroup_Email_Model_Adminhtml_Source_Datafields
{
    /**
     *  Returns the account's datafields.
     * @return array
     */
    public function toOptionArray()
    {
        $fields = array();
        $helper = Mage::helper('connector');

        $website = Mage::app()->getRequest()->getParam('website');
        $client = Mage::getModel('email_connector/apiconnector_client');

        $client->setApiUsername($helper->getApiUsername($website));
        $client->setApiPassword($helper->getApiPassword($website));


        /**
         * api get all datafields
         */
        $savedDatafields = Mage::registry('datafields');
        if($savedDatafields){
            $datafields = $savedDatafields;
        }else{
            $datafields = $client->getDataFields();
            Mage::register('datafields',  $datafields);
        }

        // Add a "Do Not Map" Option
        $fields[] = array('value' => 0, 'label' => Mage::helper('connector')->__('-- Please Select --'));

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