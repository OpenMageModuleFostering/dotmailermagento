<?php
class Dotdigitalgroup_Email_Model_Cron
{
    /**
     * CRON FOR CONTACTS SYNC
     */
    public function contactSync()
    {
        if(Mage::helper('connector')->getContactSyncEnabled()){

            // send customers
            Mage::getModel('connector/customer_contact')->sync();
        }
        return;
    }


    /**
     * CRON FOR LOST BASKET
     */
    public function lostBaskets()
    {
        // send lost basket
        //Mage::getModel('connector/email_send')->sendLostBasketsEmail();

        Mage::getModel('connector/sales_quote')->proccessCampaigns();


    }

    /**
     * CRON FOR ORDER TRANSACTIONAL DATA
     */
    public function orderSync()
    {
        if(Mage::helper('connector')->getOrderSyncEnabled()){

            // send order
            Mage::getModel('connector/sales_order')->sync();
        }
    }

    /**
     * CRON FOR SUBSCRIBERS AND SUPRESSED CONTACTS
     */
    public function subscribersAndSuppressedSync()
    {
        $helper = Mage::helper('connector');
        if($helper->getSubscriberSyncEnabled()){
            $helper->log('start subscribers and suppresssed sync..');
            //sync subscribers
            Mage::getModel('connector/newsletter_subscriber')
                ->sync()
                ->unsubscribe();
            //sync guests
            Mage::getModel('connector/customer_guest')->sync();
            $helper->log('end subscribers and suppresssed sync.');
        }
    }

    public function sendMail()
    {
        $helper = Mage::helper('connector');
        $helper->log('Sending mail cron..');
        $emailModel = Mage::getModel('connector/email_send')->send();


        $helper->log('email send end');
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