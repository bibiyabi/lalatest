<?php

namespace App\Contracts\Payments;

class CallbackResult
{
    private $success;

    private $order;

    private $amount;

    private $msg;

    /**
     * @param boolean $success
     * @param string $msg
     * @param Order|WithdrawOrder $order
     * @param float $amount
     */
    public function __construct(bool $success, string $msg='', $order=null, float $amount=0) {
        $this->success = $success;
        $this->order = $order;
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
     * Get the value of order
     */
    public function getOrder()
    {
        return $this->order;
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
