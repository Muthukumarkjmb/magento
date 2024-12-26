<?php
namespace Epi\FulfillmentApi\Api;
interface OrderInterface
{
    /**
     * GET for Post api
     * @param string $entityId
     * @return Data\OrderResponseDataInterface
     * 
     */
    public function getOrderDetails($entityId);
}