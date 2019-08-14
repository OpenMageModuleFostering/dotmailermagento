<?php

class Dotdigitalgroup_Email_DynamicContentController extends Mage_Core_Controller_Front_Action
{
	/**
	 * @return Mage_Core_Controller_Front_Action|void
	 */
	public function preDispatch()
	{
		//authenticate
		Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
		$orderId = $this->getRequest()->getParam('order_id', false);
		//check for order_id param
		if ($orderId) {
			$order = Mage::getModel('sales/order')->load($orderId);
			//check if the order still exists
			if ($order->getId()) {
				$storeId = $order->getStoreId();
				//start the emulation for order store
				$appEmulation = Mage::getSingleton('core/app_emulation');
				$appEmulation->startEnvironmentEmulation($storeId);
			} else {
				Mage::helper('connector')->log('TE invoice : order not found: ' . $orderId);
				exit;
			}
		} else {
			Mage::helper('connector')->log('TE invoice : order_id missing :' . $orderId);
			exit;
		}
		parent::preDispatch();
	}
}