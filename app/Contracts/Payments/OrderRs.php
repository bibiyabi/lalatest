<?php

namespace App\Contracts\Payments;

class OrderRs
{
    private $success;

    private $msg;

    private $status;

    public function __construct(bool $success, string $msg, int $status) {
        $this->success = $success;
        $this->msg = $msg;
        $this->status = $status;
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
}
