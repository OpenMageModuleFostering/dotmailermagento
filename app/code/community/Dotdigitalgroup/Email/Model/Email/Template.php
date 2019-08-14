<?php

class Dotdigitalgroup_Email_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    /**
     * Send transactional email to recipient
     *
     * @see Mage_Core_Model_Email_Template::sendTransactional()
     * @param   string $templateId
     * @param   string|array $sender sneder information, can be declared as part of config path
     * @param   string $email recipient email
     * @param   string $name recipient name
     * @param   array $vars varianles which can be used in template
     * @param   int|null $storeId
     * @return  Mage_Core_Model_Email_Template
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null)
    {
        if (!Mage::helper('connector/transactional')->isMapped($templateId)) {
            return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        } else {
            $this->setSentSuccess(true);
            return $this;
        }
    }

}