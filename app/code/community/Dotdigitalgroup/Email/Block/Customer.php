<?php

class Dotdigitalgroup_Email_Block_Customer extends Mage_Core_Block_Template
{

    public function getNewCustomer()
    {
        $params = Mage::app()->getRequest()->getParams();
        if(isset($params['id'])){

            $customerModel = Mage::getModel('customer/customer')->load($params['id']);

            if(! $customerModel->getEntityId()){
                Mage::helper('connector')->log('Transactional email, no customer found :' . $params['id'], null, 'email');
                exit;
            }
            return $customerModel;

        }else{
            exit;
        }
    }

}