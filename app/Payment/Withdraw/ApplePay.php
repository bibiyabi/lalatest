<?php
namespace App\Payment\Withdraw;
use App\Services\AbstractDepositPayment;

class ApplePay extends AbstractDepositPayment
{

    public function __construct() {
        echo __CLASS__.__FUNCTION__;
    }

    public function getOrderRes() {
        echo 'apple getOrderRes';
    }

    public function getRedirectType() {
        return 'curl';
    }
}
