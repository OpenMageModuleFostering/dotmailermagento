<?php

class Dotdigitalgroup_Email_Model_System_Config_Backend_Syncschedule_Customer extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH  = 'connector_sync_settings/sync_customer_schedule/cronschedule';

    /**
     * Cron settings after save
     *
     *
     */
    protected function _afterSave()
    {

        $enabled    = $this->getData('groups/sync_customer_schedule/fields/enabled/value');
        $time       = $this->getData('groups/sync_customer_schedule/fields/time/value');
        $frequency  = $this->getData('groups/sync_customer_schedule/fields/frequency/value');

        $frequencyHourly    = Dotdigitalgroup_Email_Model_System_Config_Source_Syncfrequency::CRON_HOURLY;
        $frequencyDaily     = Dotdigitalgroup_Email_Model_System_Config_Source_Syncfrequency::CRON_DAILY;
        $frequencyWeekly    = Dotdigitalgroup_Email_Model_System_Config_Source_Syncfrequency::CRON_WEEKLY;
        $frequencyMonthly   = Dotdigitalgroup_Email_Model_System_Config_Source_Syncfrequency::CRON_MONTHLY;

        if ($enabled) {
            $cronDayOfWeek = date('N');
            $cronExprArray = array(
                ($frequency == $frequencyHourly) ? intval($time[1]) : '*',    # Minute
                ($frequency == $frequencyDaily) ? intval($time[0]) : '*',     # Hour
                ($frequency == $frequencyMonthly) ? '1' : '*',                # Day of the Month
                '*',                                                          # Month of the Year
                ($frequency == $frequencyWeekly) ? $cronDayOfWeek : '*',      # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
        }
        else {
            $cronExprString = '';
        }

        try {
            // store config $cronExprString
           Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
        }catch (Exception $e) {
            Mage::throwException('Unable to save the cron expression.');
        }
    }
}