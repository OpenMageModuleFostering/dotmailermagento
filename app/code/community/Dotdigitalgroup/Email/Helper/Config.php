<?php

class Dotdigitalgroup_Email_Helper_Config
{

    /**
     * API SECTION.
     */
    //API settings
    const XML_PATH_CONNECTOR_API_ENABLED                    = 'connector_api_credentials/api/enabled';
    const XML_PATH_CONNECTOR_API_USERNAME                   = 'connector_api_credentials/api/username';
    const XML_PATH_CONNECTOR_API_PASSWORD                   = 'connector_api_credentials/api/password';
    const XML_PATH_CONNECTOR_CLIENT_ID                      = 'connector_api_credentials/oauth/client_id';
    const XML_PATH_CONNECTOR_CLIENT_SECRET_ID               = 'connector_api_credentials/oauth/client_key';

    /**
     * SMS SECTION.
     */
    //enabled
    const XML_PATH_CONNECTOR_SMS_ENABLED_1                  = 'connector_sms/sms_one/enabled';
    const XML_PATH_CONNECTOR_SMS_ENABLED_2                  = 'connector_sms/sms_two/enabled';
    const XML_PATH_CONNECTOR_SMS_ENABLED_3                  = 'connector_sms/sms_three/enabled';
    const XML_PATH_CONNECTOR_SMS_ENABLED_4                  = 'connector_sms/sms_four/enabled';
    //status
    const XML_PATH_CONNECTOR_SMS_STATUS_1                   = 'connector_sms/sms_one/status';
    const XML_PATH_CONNECTOR_SMS_STATUS_2                   = 'connector_sms/sms_two/status';
    const XML_PATH_CONNECTOR_SMS_STATUS_3                   = 'connector_sms/sms_three/status';
    const XML_PATH_CONNECTOR_SMS_STATUS_4                   = 'connector_sms/sms_four/status';
    //message
    const XML_PATH_CONNECTOR_SMS_MESSAGE_1                  = 'connector_sms/sms_one/message';
    const XML_PATH_CONNECTOR_SMS_MESSAGE_2                  = 'connector_sms/sms_two/message';
    const XML_PATH_CONNECTOR_SMS_MESSAGE_3                  = 'connector_sms/sms_three/message';
    const XML_PATH_CONNECTOR_SMS_MESSAGE_4                  = 'connector_sms/sms_four/message';

    /**
     * SYNC SECTION.
     */

    const XML_PATH_CONNECTOR_SYNC_CONTACT_ENABLED           = 'connector_sync_settings/sync/contact_enabled';
    const XML_PATH_CONNECTOR_SYNC_SUBSCRIBER_ENABLED        = 'connector_sync_settings/sync/subscriber_enabled';
    const XML_PATH_CONNECTOR_SYNC_ORDER_ENABLED             = 'connector_sync_settings/sync/order_enabled';
    const XML_PATH_CONNECTOR_SYNC_WISHLIST_ENABLED          = 'connector_sync_settings/sync/wishlist_enabled';

    const XML_PATH_CONNECTOR_CUSTOMERS_ADDRESS_BOOK_ID      = 'connector_sync_settings/address_book/customers';
    const XML_PATH_CONNECTOR_SUBSCRIBERS_ADDRESS_BOOK_ID    = 'connector_sync_settings/address_book/subscribers';
    const XML_PATH_CONNECTOR_GUEST_ADDRESS_BOOK_ID          = 'connector_sync_settings/address_book/guests';
    // Mapping
    const XML_PATH_CONNECTOR_MAPPING_LAST_ORDER_ID          = 'connector_data_mapping/customer_data/last_order_id';
    const XML_PATH_CONNECTOR_MAPPING_CUSTOMER_ID            = 'connector_data_mapping/customer_data/customer_id';
    const XML_PATH_CONNECTOR_MAPPING_CUSTOM_DATAFIELDS      = 'connector_data_mapping/customer_data/custom_attributes';
    const XML_PATH_CONNECTOR_MAPPING_CUSTOMER_STORENAME     = 'connector_data_mapping/customer_data/store_name';
    const XML_PATH_CONNECTOR_MAPPING_CUSTOMER_TOTALREFUND   = 'connector_data_mapping/customer_data/total_refund';
    // Dynamic
    const XML_PATH_CONNECTOR_DYNAMIC_CONTENT_PASSCODE = 'connector_dynamic_content/external_dynamic_content_urls/passcode';

    /**
     * ADVANCED SECTION.
     */
    const XML_PATH_CONNECTOR_ADVANCED_DEBUG_ENABLED         = 'connector_advanced_settings/admin/debug_enabled';
    const XML_PATH_CONNECTOR_SYNC_LIMIT                     = 'connector_advanced_settings/admin/batch_size';
    const XML_PATH_CONNECTOR_RESOURCE_ALLOCATION            = 'connector_advanced_settings/admin/memory_limit';
    const XML_PATH_CONNECTOR_TRANSACTIONAL_DATA_SYNC_LIMIT  = 'connector_advanced_settings/sync_limits/orders';
    const XML_PATH_CONNECTOR_TRANSACTIONAL_STYLING          = 'connector_advanced_settings/admin/inline_styling';
    const XML_PATH_CONNECTOR_RECOMMENDED_STYLING            = 'connector_advanced_settings/admin/recommended_inline';

    /**
     * ROI SECTION.
     */
    const XML_PATH_CONNECTOR_ROI_TRACKING_ENABLED           = 'connector_roi_tracking/roi_tracking/enabled';
    const XML_PATH_CONNECTOR_PAGE_TRACKING_ENABLED          = 'connector_roi_tracking/page_tracking/enabled';

    /**
     * OAUTH
     */

    const API_CONNECTOR_URL_AUTHORISE                       = 'https://my.dotmailer.com/OAuth2/authorise.aspx?';
    const API_CONNECTOR_URL_TOKEN                           = 'https://my.dotmailer.com/OAuth2/Tokens.ashx';
    const API_CONNECTOR_URL_LOG_USER                        = 'https://my.dotmailer.com/?oauthtoken=';


}