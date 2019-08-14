<?php

class Dotdigitalgroup_Email_Model_Cron
{
    /**
     * CRON FOR CONTACTS SYNC
     */
    public function contactSync()
    {
            // send customers
        Mage::getModel('email_connector/apiconnector_contact')->sync();
    }

    /**
     * CRON FOR LOST BASKET
     */
    public function lostBaskets()
    {
        // send lost basket
        Mage::getModel('email_connector/sales_quote')->proccessLostBaskets();

    }

    /**
     * CRON FOR ORDER TRANSACTIONAL DATA
     */
    public function orderSync()
    {
        // send order
        Mage::getModel('email_connector/sales_order')->sync();
    }

    /**
     * CRON FOR SUBSCRIBERS AND GUEST CONTACTS
     */
    public function subscribersAndGuestSync()
    {
        //sync subscribers
        Mage::getModel('email_connector/newsletter_subscriber')
            ->sync()
            ->unsubscribe();
        //sync guests
        Mage::getModel('email_connector/customer_guest')->sync();
    }

    /**
     * CRON FOR EMAILS SENDING
     */
    public function sendMappedEmails()
    {
        Mage::getModel('email_connector/campaign')->sendCampaigns();

        return $this;
    }

    public function createEmailsToCampaigns()
    {
        Mage::getModel('email_connector/create')->createEmailsToCampaigns();
    }

    /**
     * CLEAN ARHIVED FOLDERS
     */
    public function cleaning()
    {
        $helper = Mage::helper('connector/file');
        $archivedFolder = $helper->getArchiveFolder();
        $result = $helper->deleteDir($archivedFolder);
        $helper->log('Cleaning cronjob result : ' . $result);
        return $result;
    }

}