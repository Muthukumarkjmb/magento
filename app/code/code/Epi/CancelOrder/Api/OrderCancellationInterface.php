<?php
namespace Epi\CancelOrder\Api;
interface OrderCancellationInterface
{
    /**
     * GET for Post api
     * @param mixed $data
     * @return Data\OrderCancellationDataInterface
     * 
     */
    public function cancelOrder($data);
}