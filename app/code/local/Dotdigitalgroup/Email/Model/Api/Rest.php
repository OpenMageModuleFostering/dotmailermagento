<?php

class Dotdigitalgroup_Email_Model_Api_Rest extends Mage_Core_Model_Abstract
{
    const REST_WAIT_UPLOAD_TIME = 5;
    //rest api data
    const REST_ACCOUNT_INFO                     = 'https://apiconnector.com/v2/account-info';
    const REST_CONTACTS                         = 'https://apiconnector.com/v2/contacts/';
    const REST_CONTACTS_IMPORT                  = 'https://apiconnector.com/v2/contacts/import/';
    const REST_ADDRESS_BOOKS                    = 'https://apiconnector.com/v2/address-books';
    const REST_DATA_FILEDS                      = 'https://apiconnector.com/v2/data-fields';
    const REST_TRANSACTIONAL_DATA_IMPORT        = 'https://apiconnector.com/v2/contacts/transactional-data/import/';
    const REST_SINGLE_TRANSACTIONAL_DATA_IMPORT = 'https://apiconnector.com/v2/contacts/transactional-data/';
    const REST_CAMPAIGN_SEND                    = 'https://apiconnector.com/v2/campaigns/send';
    const REST_CONTACTS_SUPPRESSED_SINCE        = 'https://apiconnector.com/v2/contacts/suppressed-since/';
    const REST_DATA_FIELDS_CAMPAIGNS            = 'https://apiconnector.com/v2/campaigns';
    const REST_SMS_MESSAGE_SEND_TO              = 'https://apiconnector.com/v2/sms-messages/send-to/';
    //rest error responces
    const REST_CONTACT_NOT_FOUND                = 'Error: ERROR_CONTACT_NOT_FOUND';
    const REST_STATUS_IMPORT_REPORT_NOT_FOUND   = 'Import is not processed yet or completed with error. ERROR_IMPORT_REPORT_NOT_FOUND';
    const REST_STATUS_REPORT_NOTFINISHED        = 'NotFinished';
    const REST_TRANSACTIONAL_DATA_NOT_EXISTS    = 'Error: ERROR_TRANSACTIONAL_DATA_DOES_NOT_EXIST';
    const REST_API_USAGE_EXCEEDED               = 'Your account has generated excess API activity and is being temporarily capped. Please contact support. ERROR_APIUSAGE_EXCEEDED';

    protected $_api_user;
    protected $_api_password;
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
    protected  $_helper;
    public $result = array('error' => false, 'message' => '');
    protected $_log_filename = 'api.log';

    public function __construct()
    {
        // connect to default
        $this->_api_user     = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_USERNAME);
        $this->_api_password = Mage::getStoreConfig(Dotdigitalgroup_Email_Model_Customer_Customer::XML_PATH_CONNECTOR_API_PASSWORD);
        $this->_helper = Mage::helper('connector');
    }

    /**
     * Deletes all contacts from a given address book.
     * @param $addressBooks
     * @return mixed
     */
    protected function deleteAddressBookContacts($addressBooks) {

        foreach ($addressBooks as $addressBookId) {

            // skip if contact Id is null otherwise the API will delete ALL contacts from the address book!!!
            if ($addressBookId==null) continue;
            $ch = curl_init("https://apiconnector.com/v2/address-books/{$addressBookId}/contacts/");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // delete the contact from the address book
            $result = curl_exec($ch);
            $result = json_decode($result);
        }
    }


    /**
     * Bulk creates, or bulk updates, contacts. Import format can either be CSV or Excel.
     * Must include one column called "Email". Any other columns will attempt to map to your custom data fields.
     * The ID of returned object can be used to query import progress.
     * @param $filename
     * @param $addressBookId
     * @return mixed
     */
    protected function postAddressBookContactsImport($filename, $addressBookId)
    {
        // ...the API request
        $uploadUrl = "https://apiconnector.com/v2/address-books/{$addressBookId}/contacts/import";
        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array (
            'file' => '@'.$this->fileHelper->getFilePath($filename)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: multipart/form-data')
        );
        // send contacts to address book
        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }

    /**
     * Adds a contact to a given address book.
     * @param $addressBookId
     * @param $contactAPI
     * @return mixed
     */
    public function postAddressBookContacts($addressBookId, $contactAPI)
    {
        $data_string = json_encode($contactAPI);
        $url = self::REST_ADDRESS_BOOKS . '/' . $addressBookId . '/contacts';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        // send campaign
        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }

    /**
     * Deletes all contacts from a given address book.
     * @param $addressBookId
     * @param $contactId
     */
    public function deleteAddressBookContact($addressBookId, $contactId)
    {
        $url = self::REST_ADDRESS_BOOKS . '/' . $addressBookId . '/contacts/' . $contactId;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $result = curl_exec($ch);
    }

    /**
     * Gets a report with statistics about what was successfully imported, and what was unable to be imported.
     * @param $importId
     * @return mixed
     */
    public function getContactsImportReport($importId)
    {
        $reportUrl = self::REST_CONTACTS_IMPORT . "{$importId}/report";
        $ch = curl_init($reportUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
        );
        // get the report
        $result = curl_exec($ch);
        $result = json_decode($result);
        return $result;
    }

    public function getContacts($skip = 0, $limit = 1000)
    {
        $allContactsUrl = self::REST_CONTACTS . '?select=' . $limit . '&skip='  . $skip;
        $ch = curl_init($allContactsUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $data = curl_exec($ch);
        $result  = json_decode($data);

        return $result;
    }

    public function getContactByEmail($email)
    {
        $contactInfoUrl = self::REST_CONTACTS . $email;
        $ch = curl_init($contactInfoUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $data = curl_exec($ch);
        $result  = json_decode($data);

        return $result;
    }
    public function getContactById($contactId)
    {
        $contactInfoUrl = self::REST_CONTACTS . $contactId;
        $ch = curl_init($contactInfoUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $data = curl_exec($ch);
        $result  = json_decode($data);

        return $result;
    }

    /**
     * Creates an address book.
     * @return mixed
     */
    public function postAddressBooks()
    {
        $contactInfoUrl = self::REST_ADDRESS_BOOKS;
        $ch = curl_init($contactInfoUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $data = curl_exec($ch);
        $result  = json_decode($data);
        return $result;
    }

    /**
     * Creates a campaign.
     * @return mixed
     */
    protected function postCampaigns(){
        $contactInfoUrl = self::REST_DATA_FIELDS_CAMPAIGNS;
        $ch = curl_init($contactInfoUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $data = curl_exec($ch);
        $result  = json_decode($data);
        return $result;
    }

    /**
     * Creates a data field within the account.
     * @return mixed
     */
    protected function postDataFields() {
        $contactInfoUrl = self::REST_DATA_FILEDS;
        $ch_contact = curl_init($contactInfoUrl);
        curl_setopt($ch_contact, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch_contact, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch_contact, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_contact, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_contact, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $data = curl_exec($ch_contact);
        $result  = json_decode($data);
        return $result;
    }

    /**
     * Bulk creates, or bulk updates, contacts. Import format can either be CSV or Excel.
     * Must include one column called "Email". Any other columns will attempt to map to your custom data fields.
     * The ID of returned object can be used to query import progress.
     * @param bool $waitFinished
     * @return mixed
     */
    private function postContactsImport($waitFinished = false){
        $importUrl = self::REST_CONTACTS_IMPORT;
        $ch = curl_init($importUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array (
            'file' => '@'.$this->getFilePath($this->_filename)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: multipart/form-data')
        );
        $result = curl_exec($ch);
        $result = json_decode($result);

        if($waitFinished)
            $this->waitFinishedImport($result->id);
        return $result;
    }
    public function updateContact($contactId, $data)
    {
        $data_string = json_encode($data);
        $contactInfoUrl = self::REST_CONTACTS . $contactId;
        $ch = curl_init($contactInfoUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $data = curl_exec($ch);
        $result  = json_decode($data);
        return $result;
    }

    /**
     * Send connector campaings
     * @param $campaignId
     * @param $contacts
     * @return mixed
     */
    public function sendCampaign($campaignId, $contacts)
    {
        $data = array(
            'username' => $this->_api_user,
            'password' => $this->_api_password,
            "campaignId" => $campaignId,
            "ContactIds" => $contacts
        );

        $this->_helper->log($data, null, $this->_log_filename);
        $data_string = json_encode($data);
        $campaignUrl = self::REST_CAMPAIGN_SEND;
        $ch = curl_init($campaignUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        // send campaign
        $result = curl_exec($ch);
        $result = json_decode($result);
        $this->_helper->log($result, null, $this->_log_filename);

        return $result;
    }
    private function waitFinishedImport($importId){

        do{
            // wait until do the report status call
            sleep(self::REST_WAIT_UPLOAD_TIME);
            // ...the API request
            // Create a GET request

            $reportUrl = self::REST_CONTACTS_IMPORT . "{$importId}/report";
            $ch = curl_init($reportUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            // get the report
            $result = curl_exec($ch);
            $result = json_decode($result);

        }while(isset($result->message) && $result->message == self::REST_STATUS_REPORT_NOTFINISHED);

    }

    /**
     * create new connector contact
     * @param $email
     * @return mixed
     */
    public function createNewContact($email)
    {
        $data = array(
            'Email' => $email,
            'EmailType' => 'Html'
        );
        $data_string = json_encode($data);
        $ch = curl_init(self::REST_CONTACTS);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }

    public function sendIntallInfo($testEmail, $contactId, $campaignId)
    {
        $numProducts = Mage::getModel('catalog/product')->getCollection()->getSize();
        $numCustomers = Mage::getModel('customer/customer')->getCollection()->getSize();

        $data = array(
            'Email' => $testEmail,
            'EmailType' => 'Html',
            'DataFields' => array(
                array(
                    'Key' => 'INSTALLCUSTOMERS',
                    'Value' => (string)$numCustomers),
                array(
                    'Key' => 'INSTALLPRODUCTS',
                    'Value' => (string)$numProducts),
                array(
                    'Key' => 'INSTALLURL',
                    'Value' => Mage::getBaseUrl()),
                array(
                    'Key' => 'INSTALLAPI',
                    'Value' => implode(',' , $this->getWebsiteAccounts())),
                array(
                    'Key' => 'PHPMEMORY',
                    'Value' => ini_get('memory_limit') . ', V=' . Mage::helper('connector')->getConnectorVersion()
                )
            )
        );

        $this->updateContact($contactId, $data);
        $this->sendCampaign($campaignId, array($contactId));

        return ;
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

    protected function getSuppressedSince($dateString)
    {
        $suppressedUrl = self::REST_CONTACTS_SUPPRESSED_SINCE . $dateString;
        $ch = curl_init($suppressedUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        // get the list of suppressed contacts
        $data = curl_exec($ch);
        $data = json_decode($data);

        return $data;

    }
    public function sendMultiTransactionalData($ordersData, $collectionName = 'Order')
    {
        $orders = array();
        foreach ($ordersData as $order) {
            if(isset($order->connector_id)){
                $orders[] = array(
                    'Key' => $order->id,
                    'ContactIdentifier' => $order->connector_id,
                    'Json' => json_encode($order->expose())
                );
            }
        }

        $data_string = json_encode($orders);
        $transactionalUrl = self::REST_TRANSACTIONAL_DATA_IMPORT . $collectionName;

        $ch = curl_init($transactionalUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        // send contacts to connector
        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }
    protected function sendTransactionalData($quoteData, $collectionName = 'Basket')
    {
        // check if the transactional data is already set
        $transactionalUrl = self::REST_SINGLE_TRANSACTIONAL_DATA_IMPORT . $collectionName . '/' . $quoteData->id ;
        $ch = curl_init($transactionalUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $result = curl_exec($ch);
        $result = json_decode($result);

        if(isset($result->message) && $result->message == self::REST_TRANSACTIONAL_DATA_NOT_EXISTS)
            $transactionalUrl = self::REST_SINGLE_TRANSACTIONAL_DATA_IMPORT . $collectionName ;
        else
            $transactionalUrl = self::REST_SINGLE_TRANSACTIONAL_DATA_IMPORT . $collectionName . '/' . $result->key ;


        $data = array(
            'Key' => $quoteData->id,
            'ContactIdentifier' => $quoteData->connector_id,
            'Json' => json_encode($quoteData->expose())
        );


        $data_string = json_encode($data);

        $ch = curl_init($transactionalUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        // send contacts to connector
        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }

    public function sendOrderTransactionalData($order, $collectionName = 'Order', $key = '')
    {
        $data = array(
            'Key' => $order->id,
            'ContactIdentifier' => $order->connector_id,
            'Json' => json_encode($order->expose())
        );


        $data_string = json_encode($data);

        $transactionalUrl = self::REST_SINGLE_TRANSACTIONAL_DATA_IMPORT . $collectionName . '/' . $key;

        $ch = curl_init($transactionalUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        // send contacts to connector
        $result = curl_exec($ch);
        $result = json_decode($result);

        if(! isset($result->message)){
            $this->removeTransactionalData($order->connector_id, 'Basket');
        }

        return $result;
    }
    public function deleteContactsTransactionalData($collectionName = 'Order', $key = '')
    {

        $transactionalUrl = self::REST_SINGLE_TRANSACTIONAL_DATA_IMPORT . $collectionName . '/' . $key;

        $ch = curl_init($transactionalUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // send contacts to connector
        $result = curl_exec($ch);
        $result = json_decode($result);


        return $result;
    }

    public function removeTransactionalData($contactId, $collectionName)
    {
        $transactionalUrl =  'https://apiconnector.com/v2/contacts/' . $contactId . '/transactional-data/' . $collectionName ;
        $ch = curl_init($transactionalUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $result = curl_exec($ch);

    }

    public function testAccount()
    {
        $url = self::REST_ACCOUNT_INFO;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $result = json_decode($result);
        if(isset($result->message)){
            return false;
        }

        return true;
    }

    public function getAddressBookContacts($bookId, $skip = 0)
    {
        /**
         * https://apiconnector.com/v2/address-books/{addressBookId}/contacts?withFullData={withFullData}&select={select}&skip={skip}
         */
        $url = self::REST_ADDRESS_BOOKS . '/' . $bookId . '/contacts' . '?skip=' . $skip;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $result = json_decode($result);
        return $result;

    }

    /**
     * Send a single SMS message.
     * @param $number
     * @param $message
     * @return mixed
     */
    public function postSmsMessagesSendTo($telephoneNumber, $message)
    {
        $data = array('Message' => $message);
        $data_string = json_encode($data);
        $ch = curl_init(self::REST_SMS_MESSAGE_SEND_TO . $telephoneNumber);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        $result = json_decode($result);
        return $result;
    }

    public function postContactsTransactionalDataImport($collectionName, $data = array())
    {
        $import = array();

        foreach ($data as $one) {

                $import[] = array(
                    'Key' => $one->getId(),
                    'ContactIdentifier' => $one->getCustomerId(),
                    'Json' => json_encode($one->expose())
                );
        }

        $data_string = json_encode($import);

        $transactionalUrl = self::REST_TRANSACTIONAL_DATA_IMPORT . $collectionName;

        $ch = curl_init($transactionalUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERPWD, $this->_api_user . ':' . $this->_api_password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        // send contacts to connector
        $result = curl_exec($ch);
        $result = json_decode($result);

        return $result;
    }
}
