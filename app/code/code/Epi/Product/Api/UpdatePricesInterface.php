<?php
namespace Epi\Product\Api;
interface UpdatePricesInterface
{
    /**
     * GET for Post api
     * @return Data\PricesUpdateResponseDataInterface
     * 
     */
    public function updatePrices();
}