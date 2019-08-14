<?php

class Dotdigitalgroup_Email_Model_Customer_Guest
{
    protected $_countGuests = 0;
    protected $_start;

    public function sync()
    {
        /** @var Dotdigitalgroup_Email_Helper_Data $helper */
        $helper = Mage::helper('connector');
        $helper->log('----------- Start guest sync ----------');
        $this->_start = microtime(true);
        foreach(Mage::app()->getWebsites() as $website)
            $this->_exportGuestPerWebsite($website);
        $helper->log('---- End Guest total time for guest sync : ' . gmdate("H:i:s", microtime(true) - $this->_start));
        return;
    }

    public function _exportGuestPerWebsite(Mage_Core_Model_Website $website)
    {
        $helper = Mage::helper('connector');
        $fileHelper = Mage::helper('connector/file');
        $guests = Mage::getModel('email_connector/contact')->getGuests($website);
        if($guests->getSize()){
            $client = Mage::helper('connector')->getWebsiteApiClient($website);
            $guestFilename = strtolower($website->getCode() . '_guest_' . date('d_m_Y_Hi') . '.csv');
            $helper->log('Guest file: ' . $guestFilename);
            $storeName = $helper->getMappedStoreName($website);
            $fileHelper->outputCSV($fileHelper->getFilePath($guestFilename), array('Email', 'emailType', $storeName));
            foreach ($guests as $guest) {
                $email = $guest->getEmail();
                try{
                    $guest->setEmailImported(Dotdigitalgroup_Email_Model_Contact::EMAIL_CONTACT_IMPORTED)
                        ->save();
                    $storeName = $website->getName();
                    // save data for guests
                    $fileHelper->outputCSV($fileHelper->getFilePath($guestFilename), array($email, 'Html', $storeName));
                    $this->_countGuests++;
                }catch (Exception $e){
                    Mage::logException($e);
                }
            }
            if($this->_countGuests){
                //Add to guest address book
                $client->postAddressBookContactsImport($guestFilename, $helper->getGuestAddressBook($website));
            }
            //arhive guest file
            $fileHelper->archiveCSV($guestFilename);
        }
    }
}