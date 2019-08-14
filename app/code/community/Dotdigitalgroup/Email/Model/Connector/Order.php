<?php

class Dotdigitalgroup_Email_Model_Connector_Order
{
    public  $id;
    public  $connector_id;
    public  $quote_id;
    protected $store_name;
    protected $purchase_date;
    protected $delivery_address;
    protected $billing_address;
    protected $products = array();
    protected $order_subtotal;
    protected $discount_ammount;
    protected $order_total;
    protected $categories;


    /**
     * set the order information
     * @param Mage_Sales_Model_Order $orderData
     */
    public function __construct(Mage_Sales_Model_Order $orderData)
    {
        $this->id           = $orderData->getIncrementId();
        $this->store_name   = $orderData->getStoreName();

        $created_at = new Zend_Date($orderData->getCreatedAt(), Zend_Date::ISO_8601);
        $this->purchase_date = $created_at->toString(Zend_Date::ISO_8601);

        $customerModel = Mage::getModel('customer/customer');
        $customerModel->load($orderData->getCustomerId());
        $this->quote_id     = $orderData->getQuoteId();

        /**
         *  billing and shipping address
         */
        //check if order has shipping data virtual/downloadable
        if($orderData->getShippingAddress()){
            $deliveryData = $orderData->getShippingAddress()->getData();
            $this->delivery_address = array(
                'delivery_address_1' => $this->getStreet($deliveryData['street'], 1),
                'delivery_address_2' => $this->getStreet($deliveryData['street'], 2),
                'delivery_city'      => $deliveryData['city'],
                'delivery_region'    => $deliveryData['region'],
                'delivery_country'   => $deliveryData['country_id'],
                'delivery_postcode'  => $deliveryData['postcode']
            );
        }

        $billingData  = $orderData->getBillingAddress()->getData();
        $this->billing_address = array(
            'billing_address_1' => $this->getStreet($billingData['street'], 1),
            'billing_address_2' => $this->getStreet($billingData['street'], 2),
            'billing_city'      => $billingData['city'],
            'billing_region'    => $billingData['region'],
            'billing_country'   => $billingData['country_id'],
            'billing_postcode'  => $billingData['postcode'],
        );

        //Order items
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

            $this->products[] = array(
                'name' => $productItem->getName(),
                'sku' => $productItem->getSku(),
                'qty' => (int)number_format($productItem->getData('qty_ordered'), 2),
                'price' => (float)number_format($productItem->getPrice(), 2),
            );
        }

        $this->order_subtotal   = (float)number_format($orderData->getData('subtotal'), 2);
        $this->discount_ammount = (float)number_format($orderData->getData('discount_amount'), 2);
        $orderTotal = abs($orderData->getData('grand_total') - $orderData->getTotalRefunded());
        $this->order_total      = (float)number_format($orderTotal, 2);

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
