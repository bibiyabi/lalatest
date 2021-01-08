<?php

namespace App\Contracts\Payments;

class OrderResult
{
    private $success;

    private $msg;

    private $errorCode;

    private $realAmount;

    public function __construct(bool $success, string $msg, int $errorCode=0, float $realAmount=0) {
        $this->success = $success;
        $this->msg = $msg;
        $this->errorCode = $errorCode;
        $this->realAmount = $realAmount;
    }

    /**
     * Get the value of success
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Get the value of msg
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * Get the value of errorCode
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get the value of realAmount
     */
    public function getRealAmount()
    {
        return $this->realAmount;
    }
}
