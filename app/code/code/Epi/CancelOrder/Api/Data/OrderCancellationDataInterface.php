<?php

namespace Epi\CancelOrder\Api\Data;

interface OrderCancellationDataInterface
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