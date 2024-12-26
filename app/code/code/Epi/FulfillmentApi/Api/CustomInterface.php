<?php
namespace Epi\FulfillmentApi\Api;
interface CustomInterface
{
    /**
     * GET for Post api
     * @param mixed $data
     * @return Data\LCAPIResponseDataInterface
     * 
     */
    public function getPost($data);
}