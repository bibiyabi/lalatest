<?php


namespace App\Contracts\Payments;


class ServiceResult
{
    private $success;
    private $errorCode;
    private $result;

    public function __construct(bool $success, int $errorCode = 0, $result = null) {
        $this->success = $success;
        $this->errorCode = $errorCode;
        $this->result = $result;
    }

    public function getSuccess()
    {
        return $this->success;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getResult()
    {
        return $this->result;
    }

}
