<?php
/**
 * mag17.
 *
 * User: chrisroseuk
 * Date: 30/04/2013
 * Time: 14:17
 * 
 */

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute('customer', 'dotmailer_contact_id', array(
    'input'         => 'text',
    'type'          => 'int',
    'label'         => 'Connector Contact ID',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 0,
));

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'dotmailer_contact_id',
    '999'  //sort_order
);

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'dotmailer_contact_id');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();

$adminData = array();
$adminData[] = array(
    'severity'      => 4,
    'date_added'    => gmdate('Y-m-d H:i:s', time()),
    'title'         => 'Email Connector was installed. Remmenber to enable cronjob to make it working.',
    'description'   => 'Connector synchronization is based on the cronjob please make sure this is setup before running through configuration.',
    'url'           => ''
);

Mage::getModel('adminnotification/inbox')->parse($adminData);


$setup->endSetup();