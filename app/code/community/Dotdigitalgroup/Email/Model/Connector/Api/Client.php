<?php

class Dotdigitalgroup_Email_Model_Connector_Api_Client extends Dotdigitalgroup_Email_Model_Abstract_Rest
{
    const APICONNECTOR_VERSION = 'V2';

    const REST_WAIT_UPLOAD_TIME = 5;
    //rest api data
    const REST_ACCOUNT_INFO                     = 'https://apiconnector.com/v2/account-info';
    const REST_CONTACTS                         = 'https://apiconnector.com/v2/contacts/';
    const REST_CONTACTS_IMPORT                  = 'https://apiconnector.com/v2/contacts/import/';
    const REST_ADDRESS_BOOKS                    = 'https://apiconnector.com/v2/address-books';
    const REST_DATA_FILEDS                      = 'https://apiconnector.com/v2/data-fields';
    const REST_TRANSACTIONAL_DATA_IMPORT        = 'https://apiconnector.com/v2/contacts/transactional-data/import/';
    const REST_TRANSACTIONAL_DATA               = 'https://apiconnector.com/v2/contacts/transactional-data/';
    const REST_CAMPAIGN_SEND                    = 'https://apiconnector.com/v2/campaigns/send';
    const REST_CONTACTS_SUPPRESSED_SINCE        = 'https://apiconnector.com/v2/contacts/suppressed-since/';
    const REST_DATA_FIELDS_CAMPAIGNS            = 'https://apiconnector.com/v2/campaigns';
    const REST_SMS_MESSAGE_SEND_TO              = 'https://apiconnector.com/v2/sms-messages/send-to/';
    //rest error responces
    const REST_CONTACT_NOT_FOUND                = 'Error: ERROR_CONTACT_NOT_FOUND';
    const REST_SEND_MULTI_TRANSACTIONAL_DATA    = 'Error: ERROR_FEATURENOTACTIVE';
    const REST_STATUS_IMPORT_REPORT_NOT_FOUND   = 'Import is not processed yet or completed with error. ERROR_IMPORT_REPORT_NOT_FOUND';
    const REST_STATUS_REPORT_NOTFINISHED        = 'NotFinished';
    const REST_TRANSACTIONAL_DATA_NOT_EXISTS    = 'Error: ERROR_TRANSACTIONAL_DATA_DOES_NOT_EXIST';
    const REST_API_USAGE_EXCEEDED               = 'Your account has generated excess API activity and is being temporarily capped. Please contact support. ERROR_APIUSAGE_EXCEEDED';
    const REST_API_EMAIL_NOT_VALID              = 'Email is not a valid email address. ERROR_PARAMETER_INVALID';

    protected $_customers_file_slug   = 'customer_sync';
    protected $_subscribers_file_slug = 'subscriber_sync';
    protected $_api_helper;
    protected $_subscribers_address_book_id;
    protected $_customers_address_book_id;
    protected $_filename;
    protected $_subscribers_filename;
    protected $_customers_filename;
    protected $_limit = 10;
    protected $_address_book_id;
    public $fileHelper;    /** @var  Dotdigitalgroup_Email_Helper_File */
    public $result = array('error' => false, 'message' => '');


    /**
     * Gets a contact by ID. Unsubscribed or suppressed contacts will not be retrieved.
     * @param $id
     * @return null
     */
    public function getContactById($id)
    {
        $url = self::REST_CONTACTS . $id;

        $this->setUrl($url)
            ->setVerb('GET');
        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET CONTACT INFO ID ' . $response->message);

        return $response;
    }

    /**
     * * Bulk creates, or bulk updates, contacts. Import format can either be CSV or Excel.
     * Must include one column called "Email". Any other columns will attempt to map to your custom data fields.
     * The ID of returned object can be used to query import progress.
     * @param $filename
     * @param $addressBookId
     * @return mixed
     */
    public function postAddressBookContactsImport($filename, $addressBookId)
    {
        // ...the API request
        $url = "https://apiconnector.com/v2/address-books/{$addressBookId}/contacts/import";
        $helper = Mage::helper('connector');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->getApiUsername() . ':' . $this->getApiPassword());
        curl_setopt($ch, CURLOPT_POSTFIELDS, array (
            'file' => '@'.Mage::helper('connector/file')->getFilePath($filename)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: multipart/form-data')
        );


        // send contacts to address book
        $result = curl_exec($ch);
        $result = json_decode($result);

        if(isset($result->message)){
            $helper->log('POST ADDRESS BOOK CONTACT IMPORT ; ' . $result->message);
        }

        return $result;
    }

    /**
     * Adds a contact to a given address book.
     * @param $addressBookId
     * @param $apiContact
     * @return mixed|null
     */
    public function postAddressBookContacts($addressBookId, $apiContact)
    {
        $url = self::REST_ADDRESS_BOOKS . '/' . $addressBookId . '/contacts';
        $this->setUrl($url)
            ->setVerb("POST")
            ->buildPostBody($apiContact);

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('POST ADDRESS BOOK CONTACTS ' . $response->message);

        return $response;
    }

    /**
     * Deletes all contacts from a given address book.
     * @param $addressBookId
     * @param $contactId
     * @return null
     */
    public function deleteAddressBookContact($addressBookId, $contactId)
    {
        $url = self::REST_ADDRESS_BOOKS . '/' . $addressBookId . '/contacts/' . $contactId;
        $this->setUrl($url)
            ->setVerb('DELETE');

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('DELETE ADDRESS BOOK CONTACT ' . $response->message);

        return $response;
    }

    /**
     * Gets a report with statistics about what was successfully imported, and what was unable to be imported.
     * @param $importId
     * @return mixed
     */
    public function getContactsImportReport($importId)
    {
        $url = self::REST_CONTACTS_IMPORT . $importId . "/report";

        $this->setUrl($url)
            ->setVerb('GET');
        
        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET CONTACTS IMPORT REPORT  . ' . $url . ' message : ' . $response->message);
        return $response;
    }

    /**
     * Gets a contact by email address.
     * @param $email
     * @return mixed
     */
    public function getContactByEmail($email)
    {
        $url = self::REST_CONTACTS . $email;
        $this->setUrl($url)
            ->setVerb('GET');

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET CONTACT BY EMAIL : ' . $email . ' ' . $response->message);

        return $response;
    }

    /**
     * Get all address books
     * @return null
     */
    public function getAddressBooks()
    {
        $url = self::REST_ADDRESS_BOOKS;
        $this->setUrl($url)
            ->setVerb("GET");

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET ALL ADDRESS BOOKS..' . $response->message);
        return $response;
    }

    /**
     * Get list of all campaigns
     * @return mixed
     */
    public function getCampaigns()
    {
        $url = self::REST_DATA_FIELDS_CAMPAIGNS;
        $this->setUrl($url)
            ->setVerb('GET');

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET CAMPAINGS ' . $response->message);

        return $response;
    }

    /**
     * Creates a data field within the account.
     * @param $data  string/array
     * @param string $type string, numeric, date, boolean
     * @param string $visibility public, private
     * @param bool $defaultValue
     * @return mixed
     */
    public function postDataFields($data, $type = 'string', $visibility = 'public', $defaultValue = false)
    {
        $url = self::REST_DATA_FILEDS;
        $helper = Mage::helper('connector');
        if($type == 'numeric' && !$defaultValue)
            $defaultValue = 0;

        if(is_string($data)){
            $data = array(
                'name' => $data,
                'type' => $type,
                'visibility' => $visibility,
                'defaultValue' => $defaultValue
            );
        }
        $this->flush();
        $this->setUrl($url)
            ->buildPostBody($data)
            ->setVerb('POST');

        $response = $this->execute();
        if(isset($response->message)){
            $helper->log('POST CREATE DATAFIELDS ' . $response->message);
            $helper->log($response);
        }

        return $response;
    }

    public function deleteDataField($name)
    {
        $url = self::REST_DATA_FILEDS . '/' . $name;
        $request = Mage::helper('connector/api_restrequest');
        $request->setUrl($url)
            ->setVerb('DELETE');

        $response = $request->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('DELETE DATA FIELD :' . $name . ' '  . $response->message);
        return $request->execute();
    }

    /**
     * Lists the data fields within the account.
     * @return mixed
     */
    public function getDataFields()
    {
        $url = self::REST_DATA_FILEDS;
        $this->setUrl($url)
            ->setVerb('GET');

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET ALL DATAFIELDS ' . $response->message);

        return $response;
    }

    /**
     * Updates a contact.
     * @param $contactId
     * @param $data
     * @return object
     */
    public function updateContact($contactId, $data)
    {
        $url = self::REST_CONTACTS . $contactId;
        $this->flush();
        $this->setUrl($url)
            ->setVerb('PUT')
            ->buildPostBody($data);

        $response = $this->execute();
        if(isset($response->message)){
            Mage::helper('connector')->log('UPDATE CONTACT  ' . $contactId);
            Mage::helper('connector')->log($data);
        }

        return $response;
    }

    /**
     * Sends a specified campaign to one or more address books, segments or contacts at a specified time.
     * Leave the address book array empty to send to All Contacts.
     * @param $campaignId
     * @param $contacts
     * @return mixed
     */
    public function postCampaignsSend($campaignId, $contacts)
    {
        $helper = Mage::helper('connector');
        $data = array(
            'username' => $this->api_username,
            'password' => $this->api_password,
            "campaignId" => $campaignId,
            "ContactIds" => $contacts
        );
        $this->setUrl(self::REST_CAMPAIGN_SEND)
            ->setVerb('POST')
            ->buildPostBody($data);

        $response = $this->execute();
        if(isset($response->message))
            $helper->log(' SENDING CAMPAIGN ' .  $response->message);

        return $response;
    }

    /**
     * Creates a contact.
     * @param $email
     * @return mixed
     */
    public function postContacts($email)
    {
        $url = self::REST_CONTACTS;
        $data = array(
            'Email' => $email,
            'EmailType' => 'Html',

        );
        $this->setUrl($url)
            ->setVerb('POST')
            ->buildPostBody($data);

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('CREATE A NEW CONTACT : ' . $email . ' ' . $response->message);

        return $response;
    }

    /**
     * @param $testEmail
     * @param $contactId
     * @param $campaignId
     */
    public function sendIntallInfo($testEmail, $contactId, $campaignId)
    {
        $helper = Mage::helper('connector');
        $productSize= Mage::getModel('catalog/product')->getCollection()->getSize();
        $customerSize = Mage::getModel('customer/customer')->getCollection()->getSize();

        $data = array(
            'Email' => $testEmail,
            'EmailType' => 'Html',
            'DataFields' => array(
                array(
                    'Key' => 'INSTALLCUSTOMERS',
                    'Value' => (string)$customerSize),
                array(
                    'Key' => 'INSTALLPRODUCTS',
                    'Value' => (string)$productSize),
                array(
                    'Key' => 'INSTALLURL',
                    'Value' => Mage::getBaseUrl()),
                array(
                    'Key' => 'INSTALLAPI',
                    'Value' => implode(',' , $this->getWebsiteAccounts())),
                array(
                    'Key' => 'PHPMEMORY',
                    'Value' => ini_get('memory_limit') . ', Version = ' . $helper->getConnectorVersion()
                )
            )
        );
        $helper->log('SEND INSTALL INFO DATA...', Zend_Log::INFO, 'api.log');
        /**
         * Update data fields for a contact
         */
        $this->updateContact($contactId, $data);
        /**
         * Send Install info campaign
         */
        $this->postCampaignsSend($campaignId, array($contactId));

        return;
    }
    private function getWebsiteAccounts()
    {
        $accounts = array();
        $websites = Mage::app()->getWebsites();
        foreach ($websites as $website) {
            $websiteId = $website->getWebsiteId();
            $websiteModel = Mage::app()->getWebsite($websiteId);
            $apiUsername = $websiteModel->getConfig('connector_api_settings/api_credentials/username');
            if(! in_array($apiUsername, $accounts))
                $accounts[] = $apiUsername;
        }
        return $accounts;
    }

    /**
     * Gets a list of suppressed contacts after a given date along with the reason for suppression.
     * @param $dateString
     * @return mixed
     */
    public function getContactsSuppressedSinceDate($dateString)
    {
        $url = self::REST_CONTACTS_SUPPRESSED_SINCE . $dateString;
        $this->setUrl($url)
            ->setVerb("GET");

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET CONTACTS SUPPRESSED SINSE : ' . $dateString . ' ' . $response->message);

        return $response;
    }

    /**
     * Adds multiple pieces of transactional data to contacts asynchronously, returning an identifier that can be used to check for import progress.
     * @param $collectionName
     * @param $transactionalData
     * @return object
     */
    public function postContactsTransactionalDataImport($transactionalData, $collectionName = 'Orders')
    {
        $orders = array();

        foreach($transactionalData as $one){
            if(isset($one->connector_id)){
                $orders[] = array(
                    'Key' => $one->id,
                    'ContactIdentifier' => $one->connector_id,
                    'Json' => json_encode($one->expose())
                );
            }
        }
        $url = self::REST_TRANSACTIONAL_DATA_IMPORT . $collectionName;
        $this->setURl($url)
            ->setVerb('POST')
            ->buildPostBody($orders);

        $result = $this->execute();
        if(isset($result->message)){
            Mage::helper('connector')->log(' SEND MULTI TRANSACTIONAL DATA ' . $result->message);
            Mage::helper('connector')->log($orders);
        }

        return $result;
    }

    /**
     *  Adds a single piece of transactional data to a contact.
     * @param $data
     * @param string $name
     * @return null
     */
    public function postContactsTransactionalData($data, $name = 'Orders')
    {

        $getData = $this->getContactsTransactionalDataByKey($name, $data->id);
        if(isset($getData->message) && $getData->message == self::REST_TRANSACTIONAL_DATA_NOT_EXISTS){
            $url  = self::REST_TRANSACTIONAL_DATA . $name;
        }else{
            $url = self::REST_TRANSACTIONAL_DATA . $name . '/' . $getData->key ;
        }
        $apiData = array(
            'Key' => $data->id,
            'ContactIdentifier' => $data->connector_id,
            'Json' => json_encode($data->expose())
        );

        $this->setUrl($url)
            ->setVerb('POST')
            ->buildPostBody($apiData);
        $response = $this->execute();
        if(isset($response->message)){
            Mage::helper('connector')->log('POST CONTACTS TRANSACTIONAL DATA  ' . $response->message);
            Mage::helper('connector')->log($apiData);
        }

        return $response;
    }

    /**
     * Gets a piece of transactional data by key.
     * @param $name
     * @param $key
     * @return null
     */
    public function getContactsTransactionalDataByKey($name, $key)
    {
        $url = self::REST_TRANSACTIONAL_DATA . $name . '/' . $key;
        $this->setUrl($url)
            ->setVerb('GET');

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('GET CONTACTS TRANSACTIONAL DATA  name: ' . $name . ' key: ' . $key . ' ' .  $response->message);

        return $response;
    }

    /**
     * Deletes all transactional data for a contact.
     * @param $email
     * @param string $collectionName
     * @return object
     */
    public function deleteContactTransactionalData($email, $collectionName = 'Orders')
    {
        $url =  'https://apiconnector.com/v2/contacts/' . $email . '/transactional-data/' . $collectionName ;
        $this->setUrl($url)
            ->setVerb('DELETE');

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('DELETE CONTACT TRANSACTIONAL DATA : ' . $email . ' ' . $response->message);

        return $response;
    }

    /**
     * Gets a summary of information about the current status of the account.
     * @return mixed
     */
    public function getAccountInfo()
    {
        $url = self::REST_ACCOUNT_INFO;
        $helper = Mage::helper('connector');
        $this->setUrl($url)
            ->setVerb('GET');
        $response = $this->execute();

        $message = isset($response->message)? $response->message : '';
        $helper->log('GET ACCOUNT INFO for api user : ' . $this->getApiUsername() . ' ' . $message);

        return $response;
    }

    /**
     * Send a single SMS message.
     * @param $telephoneNumber
     * @param $message
     * @return object
     */
    public function postSmsMessagesSendTo($telephoneNumber, $message)
    {
        $data = array('Message' => $message);
        $url = self::REST_SMS_MESSAGE_SEND_TO . $telephoneNumber;
        $this->setUrl($url)
            ->setVerb('POST')
            ->buildPostBody($data);

        $response = $this->execute();
        if(isset($response->message))
            Mage::helper('connector')->log('POST SMS MESSAGE SEND to ' . $telephoneNumber . ' message: ' . $message . ' error: ' . $response->message);

        return $response;
    }


    /**
     * Deletes multiple contacts from an address book
     * @param $addressBookId
     * @param $contactIds
     * @return object
     */
    public function deleteAddressBookContactsInbulk($addressBookId, $contactIds)
    {
        $url = 'https://apiconnector.com/v2/address-books/' . $addressBookId . '/contacts/inbulk';

        $data = array('ContactIds' => array($contactIds[0]));

        $this->setUrl($url)
            ->setVerb('DELETE')
            ->buildPostBody($data);

        $result = $this->execute();
        if(isset($result->message)){
            Mage::helper('connector')->log('DELETE BULK ADDRESS BOOK CONTACTS ' . $result->message . ' address book ' . $addressBookId);

        }
        return $result;
    }
}
