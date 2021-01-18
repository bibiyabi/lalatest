<?php

namespace App\Contracts\Payments;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Http\Request;
interface PaymentInterface
{

    public function checkInputData(Request $request);

    public function dispatchOrderQueue();

    public function callbackNotifyToQueue($request);

    public function callback(Request $request , AbstractWithdrawGateway $gateway);

}
