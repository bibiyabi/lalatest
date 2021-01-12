<?php

namespace App\Contracts\Payments;

class CallbackResult
{
    private $success;

    private $orderId;

    private $amount;

    private $msg;

    public function __construct(bool $success, string $orderId, float $amount='', string $msg='') {
        $this->success = $success;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->msg = $msg;
    }

    /**
     * Get the value of success
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Get the value of orderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get the value of amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get the value of msg
     */
    public function getMsg()
    {
        return $this->msg;
    }
}
