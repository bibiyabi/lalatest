<?php
namespace  App\Services\Payments\Withdraw;

use App\Services\Payments\Withdraw\ResultMsg;

class CryptCallbackResult extends ResultMsg
{
    private $amount;
    public function __construct($code, $msg = '') {
        parent::__construct($msg);
        $this->setCode($code);
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function getCode() {
        return $this->code;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getAmount() {
        return $this->amount;
    }

}
