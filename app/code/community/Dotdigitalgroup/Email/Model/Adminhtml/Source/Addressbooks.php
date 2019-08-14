<?php

 class Dotdigitalgroup_Email_Model_Adminhtml_Source_Addressbooks
{
	 /**
	  * Returns the address books options.
	  *
	  * @return array
	  */
	 public function toOptionArray()
     {
        $fields = array();

        $website = Mage::app()->getRequest()->getParam('website');
        $client = Mage::getModel('email_connector/apiconnector_client');

        $client->setApiUsername(Mage::helper('connector')->getApiUsername($website));
        $client->setApiPassword(Mage::helper('connector')->getApiPassword($website));

        // Add a "Do Not Map" Option
        $fields[] = array('value' => 0, 'label' => Mage::helper('connector')->__('-- Please Select --'));
        $savedAddressBooks = Mage::registry('addressbooks');
        if ($savedAddressBooks) {
            $addressBooks = $savedAddressBooks;
        } else {
            // api all address books
            $addressBooks = $client->getAddressBooks();
            Mage::register('addressbooks', $addressBooks);
        }

        if (isset($addressBooks->message)) {
            $fields[] = array('value' => 0, 'label' => Mage::helper('connector')->__('-- Please Select --'));
        }

        //set up fields with book id and label
        foreach ($addressBooks as $book) {
            if(isset($book->id))
                $fields[] = array('value' => $book->id, 'label' => $book->name);
        }

        return $fields;
    }

}