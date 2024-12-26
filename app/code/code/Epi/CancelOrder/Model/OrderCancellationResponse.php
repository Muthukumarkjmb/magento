<?php

namespace Epi\CancelOrder\Model;

class OrderCancellationResponse implements
    \Epi\CancelOrder\Api\Data\OrderCancellationDataInterface
{
  private $success;
  private $message;

  public function getSuccess() {
    return $this->success;
  }

      /**
   * Set success
   *
   * @param boolean $isSuccess
   * @return boolean
   */
  public function setSuccess($isSuccess) {
      return $this->success = $isSuccess;
  }

  public function getMessage() {
      return $this->message;
  }

    /**
   * Set message
   *
   * @param string $message
   * @return string
   */
  public function setMessage($message) {
      return $this->message = $message;
  }


}