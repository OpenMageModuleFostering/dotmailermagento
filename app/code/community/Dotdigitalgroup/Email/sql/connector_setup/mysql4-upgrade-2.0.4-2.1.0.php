<?php

$installer = $this;


$installer->startSetup();

$installer->run(
    "DROP TABLE IF EXISTS {$this->getTable('email_send')};
        CREATE TABLE `{$this->getTable('email_send')}` (
          `email_send_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `campaign_id` int(10) unsigned DEFAULT NULL,
          `quote_id` int(10) unsigned DEFAULT NULL,
          `email` varchar(255) DEFAULT NULL,
          `message` varchar(255)  DEFAULT NULL,
          `customer_id` int(10) unsigned DEFAULT NULL,
          `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          `sent_at` datetime DEFAULT NULL,
          `is_sent` smallint(5) unsigned DEFAULT NULL,
          `store_id` smallint(5) unsigned DEFAULT NULL COMMENT 'Store Id',
          PRIMARY KEY (`email_send_id`),
          KEY `IDX_EMAIL_SEND_STORE_ID` (`store_id`),
          KEY `IDX_EMAIL_SEND_CAMPAIGN_ID` (`campaign_id`),
          KEY `IDX_EMAIL_SEND_EMAIL` (`email`),
          KEY `IDX_EMAIL_SEND_IS_SENT` (`is_sent`),
          CONSTRAINT `FK_EMAIL_SEND_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Email Transactional Send';"
);



$installer->endSetup();