<?php


/**
 * Contact model.
 */
class Dotdigitalgroup_Email_Model_Email_Contact extends Mage_Core_Model_Abstract
{

    const EMAIL_CONTACT_IMPORTED = 1;
    const EMAIL_CONTACT_NOT_IMPORTED = null;

    /**
     * constructor
     */
    public function _construct(){
        parent::_construct();
        $this->_init('connector/email_contact');
    }


    /**
     * Reset the imported data
     * @return int
     */
    public function resetCustomerContacts()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('is_guest', array('null' => true))
            ->addFieldToFilter('customer_id', array('notnull' => true))
            ->addFieldToFilter('email_imported', array('notnull' => true))
        ;

        $reset = 0;
        foreach($collection as $_contact){

            try{

                if($_contact->getEmailImported()){
                    $_contact->setEmailImported(Dotdigitalgroup_Email_Model_Email_Contact::EMAIL_CONTACT_NOT_IMPORTED)->save();
                    $reset++;
                }
            }catch(Exception $e){
                Mage::helper('connector')->log($e->getMessage());

            }

        }
        return $reset;
    }

    /**
     * Load contact by customer id
     * @param $customerId
     * @return mixed
     */
    public function loadByCustomerId($customerId)
    {
        $collection =  $this->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
         if($collection->count())
            return $collection->getFirstItem();
        return $this;

    }

    public function getContactsToImportForWebsite($websiteId, $pageSize = 100)
    {
        $collection =  $this->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addFieldToFilter('email_imported', array('null' => true))
            ->addFieldToFilter('customer_id', array('notnull' => true))
        ;

        $collection->getSelect()->limit($pageSize);

        return $collection;
    }

    /**
     * Get missing contacts.
     * @param $websiteId
     * @param int $pageSize
     * @return mixed
     */
    public function getMissingContacts($websiteId, $pageSize = 100)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('contact_id', array('null' => true))
            ->addFieldToFilter('suppressed', null)
            ->addFieldToFilter('website_id', $websiteId);

        $collection->getSelect()->limit($pageSize);

        return $collection->load();
    }

    /**
     * Load Contact by Email.
     * @param $email
     * @param $websiteId
     * @return $this
     */
    public function loadByCustomerEmail($email, $websiteId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('email', $email)
            ->addFieldToFilter('website_id', $websiteId)
        ;
        if($collection->count()){
            return $collection->getFirstItem();
        }else{
            $this->setEmail($email)
                ->setWebsiteId($websiteId);
        }
        return $this;
    }

    public function getSubscribersToImport($limit)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('is_subscriber', array('notnull' => true))
            ->addFieldToFilter('subscriber_imported', array('null' => true));

        $collection->getSelect()->limit($limit);

        return $collection;
    }

}