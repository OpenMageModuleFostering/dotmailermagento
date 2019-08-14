<?php

class Dotdigitalgroup_Email_Block_Adminhtml_Dashboard_Tabs_Status extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

	const CONNECTOR_DASHBOARD_PASSED     = 'available';
	const CONNECTOR_DASHBOARD_WARRNING   = 'connector_warning';
	const CONNECTOR_DASHBOARD_FAILED     = 'error';

	private $_checkpoints = array(
		'extention_installed' => 'Extension And CURL Installed',
		'cron_running' => 'Cron running',
		'address_book_mapped' => 'Address Book Mapping',
		'roi_tracking_enabled' => 'ROI And Page Tracking',
		'transactional_emails' => 'Transactional Emails',
		'file_permission_setttings' => 'File Permission Settings',
		'missing_files' => 'Missing Files',
		'contact_sync_enabled' => 'Contact Sync Enabled',
		'contact_syncing' => 'Contacts Syncing',
		'subscriber_sync_enabled' => 'Subscribers Sync Enabled',
		'subscribers_syncing' => 'Subscribers Syncing',
		'abandoned_carts_enabled' => 'Abandoned Carts Enabled',
		'data_field_mapped' => 'Data Field Mapped',
		'valid_api_credentials' => 'API Credentials',
		'order_enabled' => 'Order Sync Enabled',
		'order_syncing' => 'Orders Syncing',
        'order_delete' => 'Orders Expiry',
		'last_abandoned_cart_sent_day' => 'Last Abandoned Cart Sent Day',
		'conflict_check' => 'Conflict Check',
		'system_information' => 'System Information'
	);
    /**
     * Set the template.
     */
    public function __construct()
    {
        parent::_construct();

	    $this->setTemplate('connector/dashboard/status.phtml');
    }

    /**
     * Prepare the layout.
     *
     * @return Mage_Core_Block_Abstract|void
     * @throws Exception
     */
    protected function _prepareLayout()
    {
    }

	public function canShowTab()
	{
		return true;
	}
	public function isHidden()
	{
		return true;
	}

	public function getTabLabel()
	{
		return Mage::helper('connector')->__('Marketing Automation System Status');
	}

	public function getTabTitle()
	{
		return Mage::helper('connector')->__('Marketing Automation System Status');
	}

	/**
	 * Collapse key for the fieldset state.
	 * @param $key
	 *
	 * @return bool
	 */
	protected function _getCollapseState($key)
	{
		$extra = Mage::getSingleton('admin/session')->getUser()->getExtra();
		if (isset($extra['configState'][$key])) {
			return $extra['configState'][$key];
		}

		return false;
	}

	public function getCheckpoints() {
		return $this->_checkpoints;
	}


	public function addCheckpoint($checkpoint)
	{
		$this->_checkpoints[$checkpoint->getName()] = $checkpoint;
	}

	/**
	 * Extension modules and curl check.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function extentionInstalled()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');

		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
	          ->setTitle('Extension Status : ')
              ->setMessage('Extension active and PHP Curl extension installed.');

		$installed = (bool)Mage::getConfig()->getModuleConfig('Dotdigitalgroup_Email')->is('active', 'true');

		if (! $installed ||  ! function_exists('curl_version')) {
			$resultContent->setStyle( self::CONNECTOR_DASHBOARD_FAILED );
			$resultContent->setMessage('Sync May Not Work Properly.');
			$resultContent->setHowto((! function_exists('curl_version'))? 'PHP Curl extention not found !' : 'PHP and Curl extension installed');
		}
		return $resultContent;
	}

	/**
	 * Check cron for the customer sync.
	 * @return array
	 */
	public function cronRunning()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
			->setTitle('Cron Status : ')
			->setMessage('Cron is running.');
		$message = 'No cronjob task found. Check if cron is configured correctly.';
		$howToSetupCron = 'For more information <a href="http://www.magentocommerce.com/wiki/1_-_installation_and_configuration/how_to_setup_a_cron_job">how to setup the Magento cronjob.</a>';
		$lastCustomerSync = Mage::getModel('email_connector/cron')->getLastCustomerSync();

		if ($lastCustomerSync === false) {
			$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
				->setHowto($howToSetupCron);
		} else {
			$timespan = Mage::helper('connector')->dateDiff($lastCustomerSync);
			//last cron was less then 5min
			if ($timespan <= 5 * 60) {
				$resultContent->setTitle('Cronjob is working : ');
				$message = sprintf('(Last execution: %s minute(s) ago) ', round($timespan/60));
			} elseif ($timespan > 5 * 60 && $timespan <= 60 * 60 ) {
				//last cron execution was between 15min and 60min
				$resultContent->setTitle('Last customer sync : ' )
					->setStyle(self::CONNECTOR_DASHBOARD_FAILED);
				$message = sprintf(' %s minutes. ', round($timespan/60));
			} else {
				//last cron was more then an hour
				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
					->setHowto('Last customer sync is older than one hour.')
					->setHowto($howToSetupCron);
			}
		}

		$resultContent->setMessage($message);
		return $resultContent;
	}

	/**
	 * Address Book Mapping.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function addressBookMapped()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
			->setTitle('Configuration For Address Book Status : ')
			->setMessage('Looks Great.');

		foreach (Mage::app()->getWebsites() as $website ) {

			$websiteName = $website->getName();
			$link = Mage::helper('adminhtml')->getUrl('*/system_config/edit/section/connector_sync_settings/website/' . $website->getCode());

			$customerMapped = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMERS_ADDRESS_BOOK_ID))? true :
				'Not mapped !';
			$subscriberMapped = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SUBSCRIBERS_ADDRESS_BOOK_ID))? true :
				'Not mapped !';
			$guestMapped = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ADDRESS_BOOK_ID))? true :
				'Not mapped !';

			if ($customerMapped !== true || $subscriberMapped !== true || $guestMapped !== true) {
				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
					->setMessage('')
					->setTable(array(
					'Website' => $websiteName,
					'Customers' => ($customerMapped !== true)? $customerMapped . '<a href="' . $link . '"> Click to map</a>' : 'Mapped.',
					'Subscribers' => ($subscriberMapped !== true)? $subscriberMapped . '<a href="' . $link . '"> Click to map</a>'  : 'Mapped.',
					'Guests' => ($guestMapped !== true)? $guestMapped . '<a href="' . $link . '"> Click to map</a>' : 'Mapped.'
				));
			}
		}

		return $resultContent;
	}

	/**
	 * ROI Tracking.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
		public function roiTrackingEnabled()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
			->setTitle('ROI Tracking Status : ')
			->setMessage('Looks Great.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();

			$roiConfig    = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_ROI_TRACKING_ENABLED))? true :  'Not Mapped! ';
			$pageTracking = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_PAGE_TRACKING_ENABLED))? true : 'Not Mapped! ';
			//not mapped show options
			if ($roiConfig !== true || $pageTracking !== true) {

				//links to enable and get redirected back
				$roiUrl = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_ROI_TRACKING_ENABLED', 'website' => $website->getId()));
				$pageUrl = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_PAGE_TRACKING_ENABLED', 'website' => $website->getId()));

				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
					->setMessage('')
					->setTable(array(
						'Website' => $websiteName,
						'ROI' => ($roiConfig !== true)? $roiConfig . '<a href="' . $roiUrl . '"> Click to enable</a>' : 'Mapped.',
						'PAGE' => ($pageTracking !== true)? $pageTracking . '<a href="' . $pageUrl . '"> Click to enable</a>' : 'Mapped.'
					));
			}
		}

		return $resultContent;
	}

	/**
	 * Transactional Data.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function transactionalEmails()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		   ->setTitle('Transactional Emails Status : ')
			->setMessage('Enabled.')
		;

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$transactional = ($website->getConfig(Dotdigitalgroup_Email_Helper_Transactional::XML_PATH_TRANSACTIONAL_API_ENABLED))? true :
				'Disabled ';
			if ($transactional !== true){
				$url = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_TRANSACTIONAL_API_ENABLED', 'website' => $website->getId()));
				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
					->setMessage('')
					->setTable(array(
						'Website' => $websiteName,
						'Status' => ($transactional)? $transactional . '<a href="' . $url . '">Click to enable</a>' : 'Enabled.'
					));
			}
		}

		return $resultContent;
	}

	/**
	 * File Permissions.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function filePermissionSetttings()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
			->setTitle('Files/Folders Permission Settings : ')
			->setMessage('Looks Great.');

		/**
		 * Arhive and email export directories.
		 */
		$emailDir   = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'export' .  DIRECTORY_SEPARATOR . 'email';
		$archiveDir = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'export' .  DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . 'archive';

		$checkEmail = Mage::helper('connector/file')->checkPathPermission($emailDir);
		$checkArchive = Mage::helper('connector/file')->checkPathPermission($archiveDir);
		if($checkEmail   != Dotdigitalgroup_Email_Helper_File::FILE_FULL_ACCESS_PERMISSION || $checkArchive != Dotdigitalgroup_Email_Helper_File::FILE_FULL_ACCESS_PERMISSION) {
			$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
				->setMessage('Wrong Permission For Directory : ');

			//list of directory permission checked
			if ($checkEmail   != Dotdigitalgroup_Email_Helper_File::FILE_FULL_ACCESS_PERMISSION)
				$resultContent->setHowto( $emailDir . ' is set to : ' . $checkEmail);
			if ($checkArchive != Dotdigitalgroup_Email_Helper_File::FILE_FULL_ACCESS_PERMISSION)
				$resultContent->setHowto( $archiveDir . ' is set to : ' . $checkArchive);
		}

		return $resultContent;
	}

	/**
	 * Check for missing files.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function missingFiles()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');

		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Missing Files : ')
		              ->setMessage('Looks Great.');

		$filePath = Mage::getModuleDir('etc', Dotdigitalgroup_Email_Helper_Config::MODULE_NAME).DS.'files.yaml';
		$config = Zend_Config_Yaml::decode(file_get_contents($filePath));


		/**
		 * Code dirs.
		 */
		$etcDir         = Mage::getModuleDir('etc', Dotdigitalgroup_Email_Helper_Config::MODULE_NAME);
		$controllerDir  = Mage::getModuleDir('controllers', Dotdigitalgroup_Email_Helper_Config::MODULE_NAME);
		$sqlDir         = Mage::getModuleDir('sql', Dotdigitalgroup_Email_Helper_Config::MODULE_NAME);
		$localeDir      = Mage::getBaseDir('locale');
		$rootDir        = Mage::getModuleDir('', Dotdigitalgroup_Email_Helper_Config::MODULE_NAME);
		$blockDir       = $rootDir .DS. 'Block';
		$helperDir      = $rootDir .DS. 'Helper';
		$modelDir       = $rootDir .DS. 'Model';

		/**
		 * Design dir.
		 */
		$designDir = Mage::getBaseDir('design');

		/**
		 * Skin dir.
		 */
		$skinDir = Mage::getBaseDir('skin');

		$filesToCheck = array($config['etc'], $config['controllers'], $config['sql'], $config['locale'], $config['block'], $config['helper'], $config['model'], $config['design'], $config['skin']);
		$pathToCheck = array($etcDir, $controllerDir, $sqlDir, $localeDir, $blockDir, $helperDir, $modelDir, $designDir, $skinDir);
		foreach ( $filesToCheck as $subdir ) {
			foreach ( $subdir as $path ) {
				$file = $pathToCheck[0] . DS . str_replace( '#', DS, $path );

				if ( !file_exists( $file ) ) {
					$resultContent->setStyle( self::CONNECTOR_DASHBOARD_FAILED )
						->setMessage('')
						->setHowto('File not found : ' . $file );
				}
			}
			array_shift($pathToCheck);
		}

		return $resultContent;
	}



	/**
	 * Contact Sync Status.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function contactSyncEnabled()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Contacts Sync Status : ')
		              ->setMessage('Looks Great.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$contact = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_CONTACT_ENABLED))? true :
				'Disabled ';
			//disabled show data table
			if ($contact !== true){
				//redirection url to enable website config
				$url = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_SYNC_CONTACT_ENABLED', 'website' => $website->getId()));
				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
					->setMessage('')
	                ->setTable(array(
		              'Website' => $websiteName,
		              'Status' => ($contact)? $contact : 'Enabled.',
		                'Fast Fix' => '<a href="' . $url . '">Click to enable</a>'
	                ));
			}
		}

		return $resultContent;
	}

	/**
	 * Check if contact is syncing by counting the number of contacts imported.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function contactSyncing()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Contacts Sync : ')
		              ->setMessage('Looks Great.');

		//duplicate email customers
		$customers = Mage::helper('connector')->getCustomersWithDuplicateEmails();
		$duplicates = $customers->count();
		if ($duplicates) {

			$customerEmails = implode(',   ', $customers->getColumnValues('email'));
			$resultContent->setHowto('Found Duplicate Customers Emails :')
				->setHowto($customerEmails);
		}

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$websiteId    = $website->getId();
			//number of customers for website
			$cusotmerForWebsite = Mage::getModel('customer/customer')->getCollection()
				->addAttributeToFilter('website_id', $websiteId)
				->getSize();
			//skip if no customers
			if (! $cusotmerForWebsite)
				continue;
			//number of contacts imported
			$contacts = Mage::getModel('email_connector/contact')->getCollection()
				->addFieldToFilter('email_imported', 1)
				->addFieldToFilter('customer_id', array('neq' => '0'))
				->addFieldToFilter('website_id', $websiteId)
				->getSize();
			$tableData = array(
				'Website' => $websiteName,
				'Status' => 'Syncing',
				'Total Customers' => $cusotmerForWebsite,
				'Imported Contacts' => $contacts
			);
			//missing contacts
			$missing = $cusotmerForWebsite - $contacts;

			//no contacts
			if (! $contacts) {

				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
					->setTitle('Contacts Sync (ignore if you have reset contacts for reimport) : ')
					->setMessage('');
				$tableData['Status'] = 'No Imported Contacts Found';
				unset($tableData['Imported Contacts']);
			} elseif ($missing) {

				$tableData['Status'] = 'Sync Not Complete';
				$tableData['Missing'] = $missing;
			}

			$resultContent->setTable($tableData);
		}

		return $resultContent;
	}

	/**
	 * Check for subscribers sync status.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function subscriberSyncEnabled()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Subscribers Sync Status : ')
		              ->setMessage('Looks Great.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$contact = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_SUBSCRIBER_ENABLED))? true :
				'Disabled ';
			//disabled show data table
			if ($contact !== true){
				//redirection url to enable website config
				$url = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_SYNC_SUBSCRIBER_ENABLED', 'website' => $website->getId()));
				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
				              ->setMessage('')
				              ->setTable(array(
					              'Website' => $websiteName,
					              'Status' => ($contact)? $contact : 'Enabled.',
					              'Fast Fix' => '<a href="' . $url . '">Click to enable</a>'
				              ));
			}
		}

		return $resultContent;

	}

	/**
	 * Subscribers syncing status.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function subscribersSyncing()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Subscribers Sync : ')
		              ->setMessage('Looks Great.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$storeIds = $website->getStoreIds();
			//number of customers for website
			$subscriberForWebsite = Mage::getModel('newsletter/subscriber')->getCollection()
                ->addFieldToFilter('store_id', array('in' => $storeIds))
				->getSize()
			;

			//skip if no subscriber
			if (! $subscriberForWebsite)
				continue;
			//number of contacts imported as subscribers
			$contacts = Mage::getModel('email_connector/contact')->getCollection()
			                ->addFieldToFilter('subscriber_imported', 1)
			                ->addFieldToFilter('is_subscriber', 1)
			                ->addFieldToFilter('store_id', array('in' => $storeIds))
			                ->getSize();
			//no contacts
			if (! $contacts) {
				$resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
	                ->setTitle('Subscriber Sync (ignore if you have reset subscribers for reimport) : ')
	                ->setMessage('')
	                ->setTable(array(
		              'Website' => $websiteName,
		              'Status' => 'No Imported Subscribers Found.'
	                ));
			}
		}

		return $resultContent;
	}

	/**
	 * Abandoned carts status.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function abandonedCartsEnabled()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');

		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
			->setTitle('Abandoned Carts Status : ')
			->setMessage('Looks Great.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$abandonedCusomer_1 = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CARTS_ENABLED_1))? true :
				'Disabled ';
			$abandonedCusomer_2 = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CARTS_ENABLED_2))? true :
				'Disabled ';
			$abandonedCusomer_3 = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CARTS_ENABLED_3))? true :
				'Disabled ';
			$abandonedGuest_1 = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ABANDONED_CARTS_ENABLED_1))? true :
				'Disabled ';
			$abandonedGuest_2 = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ABANDONED_CARTS_ENABLED_2))? true :
				'Disabled ';
			$abandonedGuest_3 = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ABANDONED_CARTS_ENABLED_3))? true :
				'Disabled ';

			if ($abandonedCusomer_1 !== true || $abandonedCusomer_2 !== true || $abandonedCusomer_3 !== true || $abandonedGuest_1 !== true || $abandonedGuest_2 !== true || $abandonedGuest_3 !== true){
				//customer abandoned links to enable
				$customer1 = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CARTS_ENABLED_1', 'website' => $website->getId()));
				$customer2 = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CARTS_ENABLED_2', 'website' => $website->getId()));
				$customer3 = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CARTS_ENABLED_3', 'website' => $website->getId()));
				//guests abandoned links to enable
				$guest1 = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_GUEST_ABANDONED_CARTS_ENABLED_1', 'website' => $website->getId()));
				$guest2 = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_GUEST_ABANDONED_CARTS_ENABLED_2', 'website' => $website->getId()));
				$guest3 = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_GUEST_ABANDONED_CARTS_ENABLED_3', 'website' => $website->getId()));


				$resultContent->setStyle( self::CONNECTOR_DASHBOARD_FAILED)
					->setMessage('Don\'t forget to map')
					->setTable(array(
						'Website' => $websiteName,
						'Customer Abandoned 1' => ($abandonedCusomer_1 !== true)? $abandonedCusomer_1 . '<a href="' . $customer1 . '">Click to enable</a>' : 'Enabled',
						'Customer Abandoned 2' => ($abandonedCusomer_2 !== true)? $abandonedCusomer_2 . '<a href="' . $customer2 . '">Click to enable</a>' : 'Enabled',
						'Customer Abandoned 3' => ($abandonedCusomer_3 !== true)? $abandonedCusomer_3 . '<a href="' . $customer3 . '">Click to enable</a>' : 'Enabled',
						'Guest Abandoned 1' => ($abandonedGuest_1 !== true)? $abandonedGuest_1 . '<a href="' . $guest1 . '">Click to enable</a>' : 'Enabled',
						'Guest Abandoned 2' => ($abandonedGuest_2 !== true)? $abandonedGuest_2 . '<a href="' . $guest2 . '">Click to enable</a>' : 'Enabled',
						'Guest Abandoned 3' => ($abandonedGuest_3 !== true)? $abandonedGuest_3 . '<a href="' . $guest3 . '">Click to enable</a>' : 'Enabled',
					));
			}
		}

		return $resultContent;
	}

	/**
	 * Crazy mapping checking.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function dataFieldMapped()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');

		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Default Datafields Mapped Status : ')
		              ->setMessage('All Datafields Are Mapped.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$passed = true;
			$mapped = 0;
			$nm = 'Not Mapped';
			$tableData = array();
			//website name for table data
			$websiteName  = $website->getName();
			$tableData['Website'] = $websiteName;
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_ID)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_FIRSTNAME)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_LASTNAME)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DOB)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_GENDER)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_WEBSITE_NAME)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_STORE_NAME)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_CREATED_AT)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_LAST_LOGGED_DATE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_CUSTOMER_GROUP)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_REVIEW_COUNT)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_LAST_REVIEW_DATE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_BILLING_ADDRESS_1)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_BILLING_ADDRESS_2)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_BILLING_CITY)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_BILLING_STATE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_BILLING_COUNTRY)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_BILLING_POSTCODE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_BILLING_TELEPHONE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DELIVERY_ADDRESS_1)) {
				$passed  = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DELIVERY_ADDRESS_2)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DELIVERY_CITY)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DELIVERY_STATE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DELIVERY_COUNTRY)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DELIVERY_POSTCODE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_DELIVERY_TELEPHONE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_TOTAL_NUMBER_ORDER)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_AOV)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_TOTAL_SPEND)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_LAST_ORDER_DATE)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_LAST_ORDER_ID)) {
				$passed = false;
				$mapped += 1;
			}
			if (! $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_MAPPING_CUSTOMER_TOTALREFUND)) {
				$passed = false;
				$mapped += 1;
			}
			$tableData['Mapped Percentage'] = number_format((1 - $mapped / 32) * 100, 2)  . ' %';
			//mapping not complete.
			if (! $passed ){
				$url = Mage::helper('adminhtml')->getUrl('*/system_config/edit/section/connector_data_mapping/website/' . $website->getCode());
				$resultContent->setStyle( self::CONNECTOR_DASHBOARD_FAILED)
					->setMessage('Click <a href="' . $url . '">here</a> to change mapping configuration.')
					;
			}
			$resultContent->setTable($tableData);
		}

		return $resultContent;

	}


	/**
	 * Validate API Credentials.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function validApiCredentials()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('API Credentials Status : ')
		              ->setMessage('Valid.');
		$helper = Mage::helper('connector');
		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$websiteId = $website->getId();

			$apiUsername = $helper->getApiUsername($websiteId);
			$apiPassword = $helper->getApiPassword($websiteId);

			$api = Mage::getModel('email_connector/apiconnector_test')->ajaxvalidate($apiUsername, $apiPassword);

			if ($api != 'Credentials Valid.'){
				$url = Mage::helper('adminhtml')->getUrl('*/system_config/edit/section/connector_api_credentials/website/' . $website->getCode());

				$resultContent->setStyle( self::CONNECTOR_DASHBOARD_FAILED)
					->setMessage('')
					->setTable(array(
						'Website' => $websiteName,
						'Status' => $api,
						'Fast Fix' => 'Click <a href="' . $url . '">here</a> to enter new api credentials.'
					));
			}
		}

		return $resultContent;


	}

	/**
	 * Order sync enabled.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function orderEnabled()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Order Sync : ')
		              ->setMessage('Enabled.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$order = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_ORDER_ENABLED))? true :
				'Disabled';

			if ($order !== true){

				$url = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_SYNC_ORDER_ENABLED', 'website' => $website->getId()));
				$resultContent->setStyle( self::CONNECTOR_DASHBOARD_WARRNING)
					->setMessage('')
					->setTable(array(
						'Website' => $websiteName,
						'Status' => $order,
						'Fast Fix' => 'Click  <a href="' . $url . '">here </a>to enable.'
					));
			}
		}

		return $resultContent;
	}

	/**
	 * Check if any orders are imported.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function orderSyncing()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Order Syncing : ')
		              ->setMessage('Looks Great.');

		foreach ( Mage::app()->getWebsites() as $website ) {
			$websiteName  = $website->getName();
			$storeIds = $website->getStoreIds();

			//numbser of orders marked as imported
			$numOrders = Mage::getModel('email_connector/order')->getCollection()
				->addFieldToFilter('email_imported', 1)
				->addFieldToFilter('store_id', array('in', $storeIds))->getSize();

			if (! $numOrders) {
				$resultContent->setStyle( self::CONNECTOR_DASHBOARD_FAILED)
					->setTitle('Order Syncing (ignore if you have reset orders for reimport) :')
					->setMessage('')
					->setTable(array(
						'Website' => $websiteName,
						'Status' => 'No Imported Orders Found'
					));
			}
		}

		return $resultContent;

	}

    /**
     *
     */
    public function orderDelete()
    {
        $resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
        $resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
            ->setTitle('Order Expiry : ')
            ->setMessage('Looks Great. ');

        foreach ( Mage::app()->getWebsites() as $website ) {
            $websiteName  = $website->getName();
            $delete = ($website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_ORDER_DELETE))? true :
                'Do Not Delete';

            if ($delete !== true){

                $url = Mage::helper('adminhtml')->getUrl('*/connector/enablewebsiteconfiguration', array('path' => 'XML_PATH_CONNECTOR_SYNC_ORDER_DELETE', 'website' => $website->getId(), 'value' => '180'));
                $resultContent->setStyle( self::CONNECTOR_DASHBOARD_WARRNING)
                    ->setMessage('')
                    ->setTable(array(
                        'Website' => $websiteName,
                        'Status' => $delete,
                        'Fast Fix' => 'Click  <a href="' . $url . '">here </a>to configure order delete.'
                    ));
            }else
                $resultContent->setMessage($resultContent->getMessage() . $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_SYNC_ORDER_DELETE) . ' Days Set.');
        }

        return $resultContent;
    }

	/**
	 * Get the last date for abandaned carts.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function lastAbandonedCartSentDay()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Last Abandoned Summary : ');

		foreach ( Mage::app()->getWebsites() as $website ) {

			$websiteName  = $website->getName();
			$client = Mage::helper('connector')->getWebsiteApiClient($website);

			//customer carts
			$customerCampaign1 = $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CAMPAIGN_1);
			$customerCampaign2 = $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CAMPAIGN_2);
			$customerCampaign3 = $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_CUSTOMER_ABANDONED_CAMPAIGN_3);

			//guests carts
			$guestCampaign1 = $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ABANDONED_CAMPAIGN_1);
			$guestCampaign2 = $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ABANDONED_CAMPAIGN_2);
			$guestCampaign3 = $website->getConfig(Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_GUEST_ABANDONED_CAMPAIGN_3);


			//date customer carts

			$cusDateSent1 = ($customerCampaign1)? $client->getCampaignSummary($customerCampaign1) : '';
			$cusDateSent2 = ($customerCampaign2)? $client->getCampaignSummary($customerCampaign2) : '';
			$cusDateSent3 = ($customerCampaign3)? $client->getCampaignSummary($customerCampaign3) : '';

			//date guest carts
			$resGuest1 = ($guestCampaign1)? $client->getCampaignSummary($guestCampaign1) : '';
			$resGuest2 = ($guestCampaign2)? $client->getCampaignSummary($guestCampaign2) : '';
			$resGuest3 = ($guestCampaign3)? $client->getCampaignSummary($guestCampaign3) : '';

			/**
			 * Customers.
			 */
			$customerCampaign1 = (isset($cusDateSent1->dateSent)? $cusDateSent1->dateSent : 'Not Sent/Selected');
			$customerCampaign2 = (isset($cusDateSent2->dateSent)? $cusDateSent2->dateSent : 'Not Sent/Selected');
			$customerCampaign3 = (isset($cusDateSent3->dateSent)? $cusDateSent3->dateSent : 'Not Sent/Selected');

			/**
			 * Guests.
			 */
			$guestCampaign1 = (isset($resGuest1->dateSent)? $resGuest1->dateSent : 'Not Sent/Selected');
			$guestCampaign2 = (isset($resGuest2->dateSent)? $resGuest2->dateSent : 'Not Sent/Selected');
			$guestCampaign3 = (isset($resGuest3->dateSent)? $resGuest3->dateSent : 'Not Sent/Selected');


			$resultContent->setTable(array(
					'Website' => $websiteName,
					'Customer Campaign 1' => $customerCampaign1,
					'Customer Campaign 2' => $customerCampaign2,
					'Customer Campaign 3' => $customerCampaign3,
					'Guest Campaign 1' => $guestCampaign1,
					'Guest Campaign 2' => $guestCampaign2,
					'Guest Campaign 3' => $guestCampaign3
				));
		}

		return $resultContent;
	}

	/**
	 * Conflict checker.
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function conflictCheck()
	{
		/**
		 * Check the API accounts for different websites and posible mapping conflicts.
		 */
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED)
		              ->setTitle('Conflict Status : ')
			->setMessage('Looks Great.')
		;

		$lastApi = false;
		foreach ( Mage::app()->getWebsites() as $website )
		{
			$apiUsername = $website->getConfig( Dotdigitalgroup_Email_Helper_Config::XML_PATH_CONNECTOR_API_USERNAME );
			if ($lastApi === false)
				$lastApi = $apiUsername;
			//check difference for the previous api usename
		 	if ($lastApi != $apiUsername) {
			    $resultContent->setStyle(self::CONNECTOR_DASHBOARD_FAILED)
				    ->setMessage('Possible configuration conflict.')
				    ->setTable( array(
				        'Website'      => $website->getName(),
				        'Multiple API Usernames' => $apiUsername
			    ));
			    $lastApi = $apiUsername;
		    }
		}

		return $resultContent;
	}

	/**
	 * System information about the version used and the memory limits.
	 *
	 * @return Dotdigitalgroup_Email_Model_Adminhtml_Dashboard_Content
	 */
	public function systemInformation()
	{
		$resultContent = Mage::getModel('email_connector/adminhtml_dashboard_content');
		$resultContent->setStyle(self::CONNECTOR_DASHBOARD_PASSED);

		//check for php version
		$resultContent->setHowTo('PHP version : V' . PHP_VERSION)
			->setHowto('PHP Memory : ' . ini_get('memory_limit'))
			->setHowto('PHP Max Execution Time : ' . ini_get('max_execution_time') . ' sec')
			->setHowto('Magento version : ' . Mage::getEdition() . ' V' . Mage::getVersion())
			->setHowto('Connector version : V' . Mage::helper('connector')->getConnectorVersion());



		return $resultContent;
	}
}