<?php

class Dotdigitalgroup_Email_Model_Connector_Order
{
    /**
     * Order Increment ID
     * @var string
     */
    public  $id;
    /**
     * Email
     * @var string
     */
    public  $email;
    /**
     * @var int
     */
    public  $quote_id;
    /**
     * @var string
     */
    public  $store_name;
    /**
     * @var string
     */
    public  $purchase_date;
    /**
     * @var string
     */
    public  $delivery_address;
    /**
     * @var string
     */
    public  $billing_address;
    /**
     * @var array
     */
    public  $products = array();
    /**
     * @var float
     */
    public  $order_subtotal;
    /**
     * @var float
     */
    public  $discount_ammount;
    /**
     * @var float
     */
    public  $order_total;
    /**
     * @var array
     */
    public  $categories;
    /**
     * Payment name
     * @var string
     */
    public  $payment;
    /**
     * @var string
     */
    public  $delivery_method;
    /**
     * @var float
     */
    public  $delivery_total;
    /**
     * @var string
     */
    public  $currency;


    public $couponCode;

    /**
     * set the order information
     * @param Mage_Sales_Model_Order $orderData
     */
    public function __construct(Mage_Sales_Model_Order $orderData)
    {
        $customerModel = Mage::getModel('customer/customer');
        $customerModel->load($orderData->getCustomerId());

        $this->id           = $orderData->getIncrementId();
        $this->quote_id     = $orderData->getQuoteId();
        $this->email        = $orderData->getCustomerEmail();
        $this->store_name   = $orderData->getStoreName();
        $created_at = new Zend_Date($orderData->getCreatedAt(), Zend_Date::ISO_8601);
        $this->purchase_date = $created_at->toString(Zend_Date::ISO_8601);
        $this->delivery_method = $orderData->getShippingDescription();
        $this->delivery_total = $orderData->getShippingAmount();
        $this->currency = $orderData->getStoreCurrencyCode();
        $this->payment = $orderData->getPayment()->getMethodInstance()->getTitle();
        $this->couponCode = $orderData->getCouponCode();

        /**
         * Billing address.
         */
        if($orderData->getBillingAddress()){
            $billingData  = $orderData->getBillingAddress()->getData();
            $this->billing_address = array(
                'billing_address_1' => $this->getStreet($billingData['street'], 1),
                'billing_address_2' => $this->getStreet($billingData['street'], 2),
                'billing_city'      => $billingData['city'],
                'billing_region'    => $billingData['region'],
                'billing_country'   => $billingData['country_id'],
                'billing_postcode'  => $billingData['postcode'],
            );
        }
        /**
         * Shipping address.
         */
        if($orderData->getShippingAddress()){
            $shippingData = $orderData->getShippingAddress()->getData();

            $this->delivery_address = array(
                'delivery_address_1' => $this->getStreet($shippingData['street'], 1),
                'delivery_address_2' => $this->getStreet($shippingData['street'], 2),
                'delivery_city'      => $shippingData['city'],
                'delivery_region'    => $shippingData['region'],
                'delivery_country'   => $shippingData['country_id'],
                'delivery_postcode'  => $shippingData['postcode']
            );
        }

        /**
         * Order items.
         */
        foreach ($orderData->getAllItems() as $productItem) {
            $product = $productItem->getProduct();
            if($product){
                // category names
                $categoryCollection = $product->getCategoryCollection()
                    ->addAttributeToSelect('name');

                foreach ($categoryCollection as $cat) {
                    $categories = array();
                    $categories[] = $cat->getName();
                    $this->categories[]['Name'] = substr(implode(', ', $categories), 0, 244);
                }
            }

            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($product->getAttributeSetId());
            $attributeSetName  = $attributeSetModel->getAttributeSetName();
            $this->products[] = array(
                'name' => $productItem->getName(),
                'sku' => $productItem->getSku(),
                'qty' => (int)number_format($productItem->getData('qty_ordered'), 2),
                'price' => (float)number_format($productItem->getPrice(), 2),
                'attribute-set' => $attributeSetName
            );
        }

        $this->order_subtotal   = (float)number_format($orderData->getData('subtotal'), 2);
        $this->discount_ammount = (float)number_format($orderData->getData('discount_amount'), 2);
        $orderTotal = abs($orderData->getData('grand_total') - $orderData->getTotalRefunded());
        $this->order_total      = (float)number_format($orderTotal, 2);

        return true;
    }
    /**
     * get the street name by line number
     * @param $street
     * @param $line
     * @return string
     */
    private  function getStreet($street, $line)
    {
        $street = explode("\n", $street);
        if($line == 1){
            return $street[0];
        }
        if(isset($street[$line -1])){

            return $street[$line - 1];
        }else{

            return '';
        }
    }
    // exposes the class as an array of objects
    public function expose() {

        return get_object_vars($this);

    }

}
