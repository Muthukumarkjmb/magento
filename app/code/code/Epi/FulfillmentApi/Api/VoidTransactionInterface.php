<?php
namespace Epi\FulfillmentApi\Api;
interface VoidTransactionInterface
{
    /**
     * GET for Post api
     * @param mixed $data
     * @return Data\LCAPIResponseDataInterface
     * 
     */
    public function voidTransaction($data);
}