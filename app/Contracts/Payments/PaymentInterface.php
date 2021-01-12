<?php

namespace App\Contracts\Payments;
use App\Services\AbstractWithdrawGateway;
interface PaymentInterface
{

    public function checkInputData($request);

    public function createToQueue();

    public function callbackNotifyToQueue($request);



}
