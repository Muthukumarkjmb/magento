<?php

namespace Epi\Product\Model;

class PricesUpdateResponse implements
    \Epi\Product\Api\Data\PricesUpdateResponseDataInterface
{
    private $success;
    private $message;
    private $orderStatus;
    private $items=[],$incrementId,$totalAmount,$taxAmount,$shippingAmount,$serviceFee,$subtotal;

    public function getSuccess() {
        return $this->success;
    }

    /**
     * Set success
     *
     * @param boolean $isSuccess
     * @return boolean
     */
    public function setSuccess($isSuccess) {
        return $this->success = $isSuccess;
    }

    public function getMessage() {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return string
     */
    public function setMessage($message) {
        return $this->message = $message;
    }
    
}