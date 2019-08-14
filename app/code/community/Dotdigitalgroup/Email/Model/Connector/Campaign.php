<?php

class Dotdigitalgroup_Email_Model_Connector_Campaign
{

    public $id;
    public $contacts = array();
    public $emails = array();
    public $email_send_id = array();

    /**
     * @param $email_send_id
     * @return $this
     */
    public function setEmailSendId($email_send_id)
    {
        $this->email_send_id[] = $email_send_id;
        return $this;
    }

    /**
     * @return array
     */
    public function getEmailSendId()
    {
        return $this->email_send_id;
    }

    /**
     * @param $contacts
     * @return $this
     */
    public function setContacts($contacts)
    {
        $this->contacts[] = $contacts;
        return $this;
    }

    /**
     * @return array
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param $emails
     * @return $this
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
        return $this;
    }

    /**
     * @return array
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }




}
