<?php
/*
* empty helper to keep admin from breaking
*/
class Dotdigitalgroup_Email_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PASSCODE                 = 'connector_advanced_settings/external/passcode';
    const XML_PATH_LAST_ORDER_NO            = 'connector_data_field_settings/customer_data/last_order_no';
    const XML_PATH_MAPPING_CUSTOMER_ID      = 'connector_data_field_settings/customer_data/customer_id';
    const XML_PATH_ENABLED_LOGS             = 'connector_advanced_settings/admin/debug';


    /**
     * return the time scheldule for the cronjob
     * @param $code
     * @return mixed
     */
    public function getSchelduledAtCronjob($code)
    {
        /* @var $collection Mage_Cron_Model_Resource_Schedule_Collection */
        $collection = Mage::getModel('cron/schedule')->getCollection();
        $collection->addFieldToFilter('job_code', $code)
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING)
            ->addOrder('scheduled_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)
            ->getSelect()->limit(1);
        $schedule = $collection->getFirstItem();

        $scheduleAt = $schedule->getData('scheduled_at');

        return $scheduleAt;
    }

    public function auth($authRequest)
    {
        if($authRequest == Mage::getStoreConfig(self::XML_PATH_PASSCODE)){
            return true;
        }

        if($this->isEnabledLogs())
            $this->log('authenication failed : ' . $authRequest , null, 'auth.log');
        exit();
    }

    public function getMappedCustomerId()
    {
        return Mage::getStoreConfig(self::XML_PATH_MAPPING_CUSTOMER_ID);
    }

    public function getPasscode()
    {
        return Mage::getStoreConfig(self::XML_PATH_PASSCODE);
    }

    public function getLastOrderNo()
    {
        return Mage::getStoreConfig(self::XML_PATH_LAST_ORDER_NO);

    }

    public function log($data, $level = Zend_Log::DEBUG, $filename = 'api.log')
    {
        if($this->isEnabledLogs()){
            $filename = 'connector_' . $filename;

            Mage::log($data, $level, $filename, $force = true);
        }
    }

    public function isEnabledLogs()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_ENABLED_LOGS);
    }

    public function isOrderTransactionalEnabled()
    {
        return (bool) Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Sales_Order::XML_PATH_TRANSACTIONAL_DATA_ENABLED);
    }

    public function isCustomerSyncEnabled()
    {
        return (bool) Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CUSTOMER_SYNC_ENABLE);
    }

    public function getConnectorVersion()
    {

        $modules = (array) Mage::getConfig()->getNode('modules')->children();

        if(isset($modules['Dotdigitalgroup_Email'])){

            $moduleName = $modules['Dotdigitalgroup_Email'];
            return $moduleName->version;
        }
        return '';
    }
}
