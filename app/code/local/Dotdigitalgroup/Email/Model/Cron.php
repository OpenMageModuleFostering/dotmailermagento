<?php
class Dotdigitalgroup_Email_Model_Cron
{
    /**
     * CRON FOR CUSTOMER SYNC
     */
    public function customersync()
    {
        if(Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CUSTOMER_SYNC_ENABLE))
            Mage::getModel('connector/customer_customer')->sync();
        return;
    }

    /**
     * CRON FOR LOST BASKET
     */
    public function lostbasketssync()
    {
        //look for lost baskets
        Mage::getModel('connector/sales_quote')->proccessCampaigns();
        $helper = Mage::helper('connector');
        if($helper->isOrderTransactionalEnabled()){
            //send transactional data and mark as imported
            Mage::getModel('connector/sales_order')->sync();
        }
        if($helper->isCustomerSyncEnabled()){
            //check for customer that have no connector id
            $numUpdated = Mage::getModel('connector/customer_customer')->syncContacts();
            Mage::helper('connector')->log('SYNC CONTACTS : '. $numUpdated);
        }
    }

    /**
     * CRON FOR SUPRESSED CONTACTS
     */
    public function suppressedsync()
    {
        if(Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CUSTOMER_SUPPRESSED_ENABLE)){
            //check suppressed contacts from connector
            Mage::getModel('connector/customer_suppressed')->unsubscribe();
        }

    }

    /**
     * CLEAN ARHIVED FOLDERS
     */

    public function cleaning()
    {
        $helper = Mage::helper('connector/file');
        $archivedFolder = $helper->getArchiveFolder();
        $result = $helper->deleteDir($archivedFolder);
        Mage::helper('connector')->log('Cleaning cronjob result : ' . $result, null, 'api.log');
        return $result;
    }

}