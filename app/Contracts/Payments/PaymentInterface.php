<?php

namespace App\Contracts\Payments;

interface PaymentInterface
{

    public function checkInputData($request);

    public function toOrderQueue();

}
