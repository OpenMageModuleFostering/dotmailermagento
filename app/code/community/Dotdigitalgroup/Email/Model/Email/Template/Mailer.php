<?php

class Dotdigitalgroup_Email_Model_Email_Template_Mailer extends Mage_Core_Model_Email_Template_Mailer
{
    private $_registered = array(
        'sales_email_order_template'                    => 'New Order',
        'sales_email_order_guest_template'              => 'New Order Guest',
        'sales_email_order_comment_template'            => 'Order Update',
        'sales_email_order_comment_guest_template'      => 'Order Update for Guest',
        'sales_email_invoice_template'                  => 'New Invoice',
        'sales_email_invoice_guest_template'            => 'New Invoice for Guest',
        'sales_email_invoice_comment_template'          => 'Invoice Update',
        'sales_email_invoice_comment_guest_template'    => 'Invoice Update for Guest',
        'sales_email_creditmemo_template'               => 'New Credit Memo',
        'sales_email_creditmemo_guest_template'         => 'New Credit Memo for Guest',
        'sales_email_creditmemo_comment_template'       => 'Credit Memo Update',
        'sales_email_creditmemo_comment_guest_template' => 'Credit Memo Update for Guest',
        'sales_email_shipment_template'                 => 'New Shipment',
        'sales_email_shipment_guest_template'           => 'New Shipment for Guest',
        'sales_email_shipment_comment_template'         => 'Shipment Update',
        'sales_email_shipment_comment_guest_template'   => 'Shipment Update for Guest',
    );
    private $_registeredCustomer = array(
        'customer_create_account_email_template'         => 'New Customer Account'
    );


    public function send()
    {
        Mage::helper('connector')->log('template id : ' . $this->getTemplateId());
        $templateParams = $this->getTemplateParams();

        //Disable the emails if the transactional data is mapped
        if (Mage::helper('connector/transactional')->isMapped($this->getTemplateId())) {
            if(array_key_exists($this->getTemplateId(), $this->_registered)){
                $this->_registerOrderCampaign($templateParams);
            }
            if(array_key_exists($this->getTemplateId(), $this->_registeredCustomer)){
                $this->_registerCustomer($templateParams);
            }
            return $this;
        }

        Mage::helper('connector')->log('NOT MAPPED : ' . $this->getTemplateId());

        $emailTemplate = Mage::getModel('core/email_template');
        // Send all emails from corresponding list
        while (!empty($this->_emailInfos)) {
            $emailInfo = array_pop($this->_emailInfos);
            // Handle "Bcc" recepients of the current email
            $emailTemplate->addBcc($emailInfo->getBccEmails());
            // Set required design parameters and delegate email sending to Mage_Core_Model_Email_Template
            $emailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                ->sendTransactional(
                    $this->getTemplateId(),
                    $this->getSender(),
                    $emailInfo->getToEmails(),
                    $emailInfo->getToNames(),
                    $this->getTemplateParams(),
                    $this->getStoreId()
                );
        }
        return $this;
    }


    public function _registerOrderCampaign($data)
    {
        $order = $data['order'];
        $storeId = $order->getStoreId();
        $campaignId = Mage::helper('connector/transactional')->getTransactionalCampaignId($this->getTemplateId(), $storeId);
        if($campaignId){
            Mage::helper('connector')->log('-- Sales Order :'  . $campaignId);
            try{
                $now = Mage::getSingleton('core/date')->gmtDate();
                //save email for sending
                $emailCampaign = Mage::getModel('email_connector/campaign');
                $emailCampaign->setOrderIncrementId($order->getRealOrderId())
                    ->setQuoteId($order->getQuoteId())
                    ->setEmail($order->getCustomerEmail())
                    ->setCustomerId($order->getCustomerId())
                    ->setStoreId($storeId)
                    ->setCampaignId($campaignId)
                    ->setEventName($this->_registered[$this->getTemplateId()])
                    ->setCreatedAt($now)
                ;
                $emailCampaign->save();
            }catch (Exception $e){
                Mage::logException($e);
            }
        }
    }

    private function _registerCustomer($data){
        $customer = $data['customer'];
        $storeId = $customer->getStoreId();
        $campaignId = Mage::helper('connector/transactional')->getTransactionalCampaignId($this->getTemplateId(), $storeId);
        if($campaignId){
            Mage::helper('connector')->log('-- Customer campaign: '  . $campaignId);
            try{
                $now = Mage::getSingleton('core/date')->gmtDate();
                //save email for sending
                $emailCampaign = Mage::getModel('email_connector/campaign');
                $emailCampaign->setEmail($customer->getEmail())
                    ->setCustomerId($customer->getId())
                    ->setStoreId($customer->getStoreId())
                    ->setCampaignId($campaignId)
                    ->setEventName($this->_registeredCustomer[$this->getTemplateId()])
                    ->setCreatedAt($now)
                ;
                $emailCampaign->save();
            }catch (Exception $e){
                Mage::logException($e);
            }
        }
    }

}