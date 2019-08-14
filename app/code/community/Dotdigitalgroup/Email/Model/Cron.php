<?php

class Dotdigitalgroup_Email_Model_Cron
{
    /**
     * CRON FOR CONTACTS SYNC
     */
    public function contactSync()
    {
        // send customers
        $result = Mage::getModel('email_connector/apiconnector_contact')->sync();
	    return $result;
    }

    /**
     * CRON FOR LOST BASKET
     */
    public function lostBaskets()
    {
	    //don't execute if the cron is running from shell
	    if (! Mage::getStoreConfigFlag(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_ABANDONED_CART_SHELL)) {
		    // send lost basket
		    Mage::getModel( 'email_connector/sales_quote' )->proccessLostBaskets();
	    }
    }

    /**
     * CRON FOR ORDER TRANSACTIONAL DATA
     */
    public function orderSync()
    {
        // send order
        $result = Mage::getModel('email_connector/sales_order')->sync();
	    return $result;
    }

    /**
     * CRON FOR SUBSCRIBERS AND GUEST CONTACTS
     */
    public function subscribersAndGuestSync()
    {
        //sync subscribers
	    $subscriberModel = Mage::getModel('email_connector/newsletter_subscriber');
        $result = $subscriberModel->sync();

	    //unsubscribe suppressed contacts
	    $subscriberModel->unsubscribe();

        //sync guests
        Mage::getModel('email_connector/customer_guest')->sync();
	    return $result;
    }

    /**
     * CRON FOR EMAILS SENDING
     */
    public function sendMappedEmails()
    {
        Mage::getModel('email_connector/campaign')->sendCampaigns();

        return $this;
    }

    /**
     * CLEAN ARHIVED FOLDERS
     */
    public function cleaning()
    {
        $helper = Mage::helper('connector/file');
	    $archivedFolder = $helper->getArchiveFolder();
	    $result = $helper->deleteDir($archivedFolder);
	    $message = 'Cleaning cronjob result : ' . $result;
	    $helper->log($message);
	    Mage::helper('connector')->rayLog('10', $message, 'model/cron.php');
        return $result;
    }


	/**
	 * Last customer sync date.
	 * @return bool|string
	 */
	public function getLastCustomerSync(){

		$schedules = Mage::getModel('cron/schedule')->getCollection();
		$schedules->getSelect()->limit(1)->order('executed_at DESC');
		$schedules->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_SUCCESS);
		$schedules->addFieldToFilter('job_code', 'connector_email_customer_sync');
		$schedules->load();

		if (count($schedules) == 0) {
			return false;
		}
		$executedAt = $schedules->getFirstItem()->getExecutedAt();
		return Mage::getModel('core/date')->date(NULL, $executedAt);
	}

}