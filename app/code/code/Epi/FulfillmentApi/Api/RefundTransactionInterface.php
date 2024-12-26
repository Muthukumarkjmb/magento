<?php
namespace Epi\FulfillmentApi\Api;
interface RefundTransactionInterface
{
    /**
     * GET for Post api
     * @param mixed $data
     * @return Data\LCAPIResponseDataInterface
     * 
     */
    public function refundTransaction($data);
}