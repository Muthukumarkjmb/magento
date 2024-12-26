<?php
namespace Epi\Product\Api;
interface UpdateProductInterface
{
    /**
     * GET for Post api
     * @param mixed $data
     * @return \Epi\FulfillmentApi\Api\Data\LCAPIResponseDataInterface
     * 
     */
    public function updateProduct($data);
}