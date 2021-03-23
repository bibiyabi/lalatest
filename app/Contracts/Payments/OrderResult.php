<?php

namespace App\Contracts\Payments;

class OrderResult
{
    private $success;

    private $msg;

    private $errorCode;

    private $result;

    public function __construct(bool $success, string $msg, int $errorCode=0, array $result=[])
    {
        $this->success = $success;
        $this->msg = $msg;
        $this->errorCode = $errorCode;
        $this->result = $result;
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
     * Get the value of result
     */
    public function getResult()
    {
        return $this->result;
    }
}
