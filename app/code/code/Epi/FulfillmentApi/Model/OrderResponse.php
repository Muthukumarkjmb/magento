<?php

namespace Epi\FulfillmentApi\Model;

class OrderResponse implements
    \Epi\FulfillmentApi\Api\Data\OrderResponseDataInterface
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
    
    public function getOrderStatus() {
            return $this->orderStatus;
    }

    /**
     * Set orderStatus
     *
     * @param string $orderStatus
     * @return string
     */
    public function setOrderStatus($orderStatus) {
        return $this->orderStatus = $orderStatus;
    }
    
    public function getItems() {
        return $this->items;
    }

    /**
     * Set items
     *
     * @param Epi\FulfillmentApi\Api\Data\OrderItemsArrayInterface $items
     * @return Epi\FulfillmentApi\Api\Data\OrderItemsArrayInterface[]
     */
    public function setItems($items) {
        return $this->items = $items;
    }

    public function getIncrementId() {
        return $this->incrementId;
    }

    /**
     * Set incrementId
     *
     * @param string $incrementId
     * @return string
     */
    public function setIncrementId($incrementId) {
        return $this->incrementId = $incrementId;
    }

    public function getTotalAmount() {
        return $this->totalAmount;
    }

    /**
     * Set totalAmount
     *
     * @param float $totalAmount
     * @return float
     */
    public function setTotalAmount($totalAmount) {
        return $this->totalAmount = $totalAmount;
    }

    public function getTaxAmount() {
        return $this->taxAmount;
    }

    /**
     * Set taxAmount
     *
     * @param float $taxAmount
     * @return float
     */
    public function setTaxAmount($taxAmount) {
        return $this->taxAmount = $taxAmount;
    }

    public function getShippingAmount() {
        return $this->shippingAmount;
    }

    /**
     * Set shippingAmount
     *
     * @param float $shippingAmount
     * @return float
     */
    public function setShippingAmount($shippingAmount) {
        return $this->shippingAmount = $shippingAmount;
    }

    public function getServiceFee() {
        return $this->serviceFee;
    }

    /**
     * Set serviceFee
     *
     * @param float $serviceFee
     * @return float
     */
    public function setServiceFee($serviceFee) {
        return $this->serviceFee = $serviceFee;
    }

    public function getSubtotal() {
        return $this->subtotal;
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     * @return float
     */
    public function setSubtotal($subtotal) {
        return $this->subtotal = $subtotal;
    }
}