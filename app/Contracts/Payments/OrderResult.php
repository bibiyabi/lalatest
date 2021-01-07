<?php

namespace App\Contracts\Payments;

class OrderResult
{
    private $success;

    private $msg;

    private $status;

    private $realAmount;

    public function __construct(bool $success, string $msg, int $status, float $realAmount=0) {
        $this->success = $success;
        $this->msg = $msg;
        $this->status = $status;
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
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the value of realAmount
     */
    public function getRealAmount()
    {
        return $this->realAmount;
    }
}
