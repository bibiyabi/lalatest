<?php

namespace App\Contracts\Payments;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Http\Request;
interface PaymentInterface
{
    public function callbackNotifyToQueue($order, $message);

}
