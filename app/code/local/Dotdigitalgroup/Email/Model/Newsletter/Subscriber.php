<?php

class Dotdigitalgroup_Email_Model_Newsletter_Subscriber extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SUBSCRIBERS_ADDRESS_BOOK_ID   = 'connector_data_field_settings/address_book/subscribers';

    // select subscribers that are not registred as customers
    public function getSubscribersNotCustomers($storeIds = array())
    {
        $newsletterCollection = Mage::getModel('newsletter/subscriber')->getCollection()
            ->addFieldToFilter('main_table.customer_id', array('eq' => 0))
            ->addFieldToFilter('subscriber_status', array('eq' => Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED))
            ->addStoreFilter($storeIds)
        ;

        return $newsletterCollection;
    }

}