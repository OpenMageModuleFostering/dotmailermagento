<?php

class Dotdigitalgroup_Email_Block_Order extends Mage_Core_Block_Template
{
    public function getNewOrder()
    {
        $params = Mage::app()->getRequest()->getParams();

        if(isset($params['id'])){

            $orderModel = Mage::getModel('sales/order')->load($params['id']);

            if(! $orderModel->getEntityId()){
                Mage::helper('connector')->log('Transactional email, no customer found :' . $params['id'], null, 'email');
                exit;
            }
            Mage::register('order_id', $params['id']);
            Mage::register('current_order', $orderModel);
            return $orderModel;

        }else{
            exit;
        }


    }
}