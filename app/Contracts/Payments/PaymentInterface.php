<?php

namespace App\Contracts\Payments;

use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use Illuminate\Http\Request;

interface PaymentInterface
{
    public function callbackNotifyToQueue($order, $message);
    public function checkInputSetDbSendOrderToQueue(Request $request);
    public function callback(Request $request, AbstractWithdrawGateway $gateway): CallbackResult;
    public function resetOrderStatus(Request $request);
    public function setCallbackDbResult(CallbackResult $res);
}
