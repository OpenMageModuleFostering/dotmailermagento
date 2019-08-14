<?php

class Dotdigitalgroup_Email_Model_Connector_Quote
{

    public  $id;
    public  $connector_id;
    public  $customer_id;
    protected $store_name;
    protected $create_date;
    protected $update_date;
    protected $delivery_address;
    protected $billing_address;
    protected $products = array();
    protected $order_subtotal;
    protected $order_total;

    /**
     * set the quote information
     * @param Mage_Sales_Model_Quote $quote
     */
    public function __construct(Mage_Sales_Model_Quote $quote)
    {
        $this->id = $quote->getId();
        $this->customer_id = $quote->getCustomerId();
        $customerModel = Mage::getModel('customer/customer')->load($this->customer_id);
        $this->connector_id = $customerModel->getData('dotmailer_contact_id');

        $this->store_name = $this->getStoreName($quote->getStoreId());
        $this->order_subtotal = (float)number_format($quote->getSubtotal(), 2);
        $this->order_total = (float)number_format($quote->getGrandTotal(), 2);

        $created_at = new Zend_Date($quote->getCreatedAt(), Zend_Date::ISO_8601);
        $updated_at = new Zend_Date($quote->getUpdatedAt(), Zend_Date::ISO_8601);
        $this->create_date = $created_at->toString(Zend_Date::ISO_8601);
        $this->update_date = $updated_at->toString(Zend_Date::ISO_8601);

        $items = $quote->getAllVisibleItems();
        foreach ($items as $product) {
            $this->products[] = array(
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'category' => $this->getCategory($product),
                'qty' => (float)number_format($product->getQty(), 2),
                'price' => (float)number_format($product->getPrice(), 2)
            );
        }
    }
    private function getStoreName($storeId)
    {
        $storeModel = Mage::getModel('core/store')->load($storeId);
        return $storeModel->getName();
    }
    private function getCategory($product)
    {
        $product = Mage::getModel('catalog/product')->load($product->getProductId());
        $categories = $product->getCategoryCollection()
            ->addAttributeToSelect('name');
        $categoryNames = array();
        foreach ($categories as $cat) {
            $categoryNames[] = $cat->getName();
        }

        $categoryNames = implode(',', $categoryNames);
        return $categoryNames;

    }
    // exposes the class as an array of objects
    public function expose() {

        return get_object_vars($this);

    }

}