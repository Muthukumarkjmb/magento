<?php

namespace Epi\FulfillmentApi\Api\Data;

interface LCAPIResponseDataInterface
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