<?php

namespace Epi\Product\Api\Data;

interface PricesUpdateResponseDataInterface
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

}