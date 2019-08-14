<?php


class Dotdigitalgroup_Email_Model_Adminhtml_Source_Dynamic_Frequency
{

	protected static $_options;

	public function toOptionArray()
	{
		if (!self::$_options) {
			self::$_options = array(
				array(
					'label' => Mage::helper('cron')->__('Daily'),
					'value' => Zend_Date::DAY,
				),
				array(
					'label' => Mage::helper('cron')->__('Weekly'),
					'value' => Zend_Date::WEEK,
				),
				array(
					'label' => Mage::helper('cron')->__('Monthly'),
					'value' => Zend_Date::MONTH,
				),
			);
		}
		return self::$_options;
	}

}
