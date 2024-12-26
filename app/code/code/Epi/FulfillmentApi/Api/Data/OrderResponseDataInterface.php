<?php

namespace Epi\FulfillmentApi\Api\Data;

interface OrderResponseDataInterface
{
    /**
    * @return boolean
    **/
    public function getSuccess();

    /**
    * @return $this
    **/
    public function setSuccess($isSuccess);

    /**
    * @return string
    **/
    public function getMessage();
    
    /**
     * @return $this
     **/
    public function setMessage($message);
    
    /**
    * @return string
    **/
    public function getOrderStatus();

    /**
    * @return $this
    **/
    public function setOrderStatus($orderStatus);
    
    /**
     * @return Epi\FulfillmentApi\Api\Data\OrderItemsArrayInterface[]
    **/
    public function getItems();

    /**
    * @return $this
    **/
    public function setItems($items);

    /**
    * @return string
    **/
    public function getIncrementId();

    /**
    * @return $this
    **/
    public function setIncrementId($incrementId);
    
    /**
    * @return float
    **/
    public function getTotalAmount();

    /**
    * @return $this
    **/
    public function setTotalAmount($totalamount);

    /**
    * @return float
    **/
    public function getTaxAmount();

    /**
    * @return $this
    **/
    public function setTaxAmount($taxAmount);

    /**
    * @return float
    **/
    public function getShippingAmount();

    /**
    * @return $this
    **/
    public function setShippingAmount($shippingAmount);

    /**
    * @return float
    **/
    public function getServiceFee();

    /**
    * @return $this
    **/
    public function setServiceFee($serviceFee);

    /**
    * @return float
    **/
    public function getSubtotal();

    /**
    * @return $this
    **/
    public function setSubtotal($subtotal);

}