<?php

class Dotdigitalgroup_Email_Model_Create extends Mage_Core_Model_Abstract
{

    const EMAIL_CREATED_TRANSFERED = 1;

    private $fromAddress;
    private $replyAction;
    private $replyAddress;

    /**
     * constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('email_connector/create');
    }

    /**
     * create emails to campaigns
     */
    public function createEmailsToCampaigns()
    {
        $helper = Mage::helper('connector/transactional');
        $emails = $this->getEmailsToCreateCampaigns();

        foreach($emails as $email)
        {
            try {
                $websiteId = $email->getWebsiteId();

                if (!$this->fromAddress)
                    $this->fromAddress = $helper->getFromAddress($websiteId);
                if (!$this->replyAction)
                    $this->replyAction = $helper->getReplyAction($websiteId);
                if ($this->replyAction == 'WebMailForward') {
                    if (!$this->replyAddress) {
                        $this->replyAddress = $helper->getReplyAddress($websiteId);
                    }
                }

                if ($this->fromAddress && $this->replyAction) {
                    $data = array(
                        'Name' => $email->getName(),
                        'Subject' => $email->getSubject(),
                        'FromName' => $email->getFromName(),
                        'FromAddress' => $this->fromAddress,
                        'HtmlContent' => $email->getHtmlContent(),
                        'PlainTextContent' => $email->getPlainTextContent(),
                        'ReplyAction' => $this->replyAction,
                        'IsSplitTest' => false,
                        'Status' => 'Unsent'
                    );
                    if ($this->replyAction == 'WebMailForward' && $this->replyAddress)
                        $data['ReplyToAddress'] = $this->replyAddress;
                    else
                        $data['ReplyToAddress'] = '';
                }

                if(isset($data)){
                    $client = Mage::helper('connector')->getWebsiteApiClient($websiteId);
                    $result = $client->postCampaign($data);
                    if (isset($result->message)) {
                        $email
                            ->setMessage($result->message)
                            ->save();
                        continue;
                    }

                    $this->_registerSendViaConnectorCampaign($result->id, $email->getEmail(), $email->getName());
                    if($email->getCopy())
                        $this->_registerSendViaConnectorCampaign($result->id, $email->getCopy(), $email->getName());

                    $email->setIsCreated(self::EMAIL_CREATED_TRANSFERED)
                        ->save();
                }
            }catch(Exception $e){
                Mage::logException($e);
            }
        }
        return;
    }

    /**
     * Save campaign
     *
     * @param $campaignId
     * @param $email
     * @param $name
     */
    protected function _registerSendViaConnectorCampaign($campaignId, $email, $name)
    {
        Mage::helper('connector')->log('-- send via connector campaign: '  . $campaignId);

        try{
            $now = Mage::getSingleton('core/date')->gmtDate();
            if($email){
                //save email for sending
                $emailCampaign = Mage::getModel('email_connector/campaign');
                $emailCampaign
                    ->setEmail($email)
                    ->setCampaignId($campaignId)
                    ->setEventName($name)
                    ->setCreatedAt($now);
                $emailCampaign->save();
            }
        }catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * get collection to create campaigns
     *
     * @param int $pageSize
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function getEmailsToCreateCampaigns($pageSize = 100)
    {
        $collection =  $this->getCollection()
            ->addFieldToFilter('is_created', array('null' => true));

        $collection->getSelect()->limit($pageSize);

        return $collection;
    }

}