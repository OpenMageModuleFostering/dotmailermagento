<?php


/**
 * Contact model.
 */
class Dotdigitalgroup_Email_Model_Contact extends Mage_Core_Model_Abstract
{

    const EMAIL_CONTACT_IMPORTED = 1;
    const EMAIL_CONTACT_NOT_IMPORTED = null;
    const EMAIL_SUBSCRIBER_NOT_IMPORTED = null;
    /**
     * constructor
     */
    public function _construct(){
        parent::_construct();
        $this->_init('email_connector/contact');
    }


    /**
     * @return $this|Mage_Core_Model_Abstract
     */
    protected function _beforeSave(){
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()){
            $this->setCreatedAt($now);
        }
        return $this;
    }

    /**
     * Reset the imported contacts
     * @return int
     */
    public function resetAllContacts()
    {

        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $coreResource->getConnection('core_write');

        try{
            $num = $conn->update($coreResource->getTableName('email_connector/contact'),
                array('email_imported' => new Zend_Db_Expr('null')),
                $conn->quoteInto('email_imported is ?', new Zend_Db_Expr('not null'))
            );
        }catch (Exception $e){
            Mage::logException($e);
        }
        /**
         * Reset subscribers.
         */
        $this->resetSubscribers();

        return $num;
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

    public function getSubscribersToImport($website, $limit = 1000)
    {
        $storeIds = $website->getStoreIds();
        $collection = $this->getCollection()
            ->addFieldToFilter('is_subscriber', array('notnull' => true))
            ->addFieldToFilter('subscriber_imported', array('null' => true))
            ->addFieldToFilter('store_id', array('in' => $storeIds))
        ;

        $collection->getSelect()->limit($limit);

        return $collection;
    }

    public function getGuests($website)
    {
        $guestCollection = $this->getCollection()
            ->addFieldToFilter('is_guest', array('notnull' => true))
            ->addFieldToFilter('email_imported', self::EMAIL_CONTACT_NOT_IMPORTED)
            ->addFieldToFilter('website_id', $website->getId())
        ;

        return $guestCollection->load();
    }

    public function getNumberOfImportedContacs()
    {

        $collection = $this->getCollection()
            ->addFieldToFilter('email_imported', array('notnull' => true));

        return $collection->getSize();
    }

    public function resetSubscribers()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('is_subscriber', array('notnull' => true))
            ->addFieldToFilter('subscriber_imported', array('notnull' => true));
        foreach ($collection as $contact) {
            $contact->setSubscriberImported(null)->save();
        }
        return $collection->count();
    }
}