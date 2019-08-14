<?php

class Dotdigitalgroup_Email_Model_Customer_Guest
{
    protected $_countGuests = 0;
    public function sync()
    {
        /** @var Dotdigitalgroup_Email_Helper_Data $helper */
        $helper = Mage::helper('connector');
        $helper->log('Start guest sync..');
        $this->_start = microtime(true);

        foreach(Mage::app()->getWebsites() as $website){

            $this->_exportGuestPerWebsite($website);
        }
        $helper->log('Total time for guest sync : ' . gmdate("H:i:s", microtime(true) - $this->_start));

        return;
    }

    public function _exportGuestPerWebsite($website)
    {
        $helper = Mage::helper('connector');
        $fileHelper = Mage::helper('connector/file');
        $client = Mage::getModel('connector/connector_api_client')
            ->setApiUsername($helper->getApiUsername($website))
            ->setApiPassword($helper->getApiPassword($website));

        $guestFilename = strtolower($website->getCode() . '_guest_' . date('d_m_Y_Hi') . '.csv');
        $helper->log('Guest file: ' . $guestFilename);

        //get store name mapped
        $storeName = $helper->getMappedStoreName($website);
        //guest file headers
        $fileHelper->outputCSV($fileHelper->getFilePath($guestFilename), array('Email', 'emailType', $storeName));

        $guests = Mage::getModel('connector/email_contact')->getCollection()
            ->addFieldToFilter('is_guest', array('notnull' => true))
            ->addFieldToFilter('is_subscriber', array('null' => true))
            ->addFieldToFilter('website_id', $website->getId())
            ->addFieldToFilter('email_imported', Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_NOT_IMPORTED)
        ;

        foreach ($guests as $guest) {


            $email = $guest->getEmail();

            $helper->log('guest  email : '. $email . ' website ' . $website->getId());
            try{

                $guest->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_IMPORTED)
                    ->save();

            }catch (Exception $e){
                $helper->log($e->getMessage());
            }

                $storeName = $website->getName();
                // save data for guests
                $fileHelper->outputCSV($fileHelper->getFilePath($guestFilename), array($email, 'Html', $storeName));
                $this->_countGuests++;
        }
        if($this->_countGuests){
            //Add to guest address book
            $client->postAddressBookContactsImport($guestFilename, $helper->getGuestAddressBook($website));
        }

        //arhive guest file
        $fileHelper->archiveCSV($guestFilename);
    }

}