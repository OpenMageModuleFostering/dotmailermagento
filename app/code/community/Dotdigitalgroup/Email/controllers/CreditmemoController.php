<?php

class Dotdigitalgroup_Email_CreditmemoController extends Mage_Core_Controller_Front_Action
{
	/**
	 * predispatch
	 *
	 * @return Mage_Core_Controller_Front_Action|void
	 */
	public function preDispatch()
    {
        Mage::helper('connector')->auth($this->getRequest()->getParam('code'));
        $orderId = $this->getRequest()->getParam('order_id', false);
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                Mage::app()->setCurrentStore($order->getStoreId());
            } else {
                Mage::helper('connector')->log('TE : order not found: ' . $orderId);
                exit;
            }
        } else {
            Mage::helper('connector')->log('TE : order_id missing :' . $orderId);
            exit;
        }
        parent::preDispatch();
    }

	/**
	 * New creditmemo.
	 */
	public function newAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order_creditmemo', 'connector_creditmemo_new', array(
            'template' => 'connector/creditmemo/new.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $items = $this->getLayout()->createBlock('email_connector/order_creditmemo', 'connector_creditmemo_items', array(
            'template' => 'connector/creditmemo/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_creditmemo_new')->append($items);
        $this->renderLayout();
    }

	/**
	 * New guest action.
	 */
	public function newguestAction()
    {
        $this->loadLayout();
        $invoice = $this->getLayout()->createBlock('email_connector/order_invoice', 'connector_creditmemo_guest', array(
            'template' => 'connector/creditmemo/newguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($invoice);
        $items = $this->getLayout()->createBlock('email_connector/order_creditmemo', 'connector_creditmemo_items', array(
            'template' => 'connector/creditmemo/items.phtml'
        ));
        $this->getLayout()->getBlock('connector_creditmemo_guest')->append($items);

        $this->renderLayout();

    }

	/**
	 * update creditmemo.
	 */
	public function updateAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order_creditmemo', 'connector_creditmemo_update', array(
            'template' => 'connector/creditmemo/update.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);

        $this->renderLayout();
    }

	/**
	 * update guest.
	 */
	public function updateguestAction()
    {
        $this->loadLayout();
        $newOrder = $this->getLayout()->createBlock('email_connector/order_creditmemo', 'connector_creditmemo_update_guest', array(
            'template' => 'connector/creditmemo/updateguest.phtml'
        ));
        $this->getLayout()->getBlock('content')->append($newOrder);
        $this->renderLayout();
    }
}