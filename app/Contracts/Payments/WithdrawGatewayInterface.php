<?php

namespace App\Contracts\Payments;

use App\Models\Order;

interface WithdrawGatewayInterface
{
    public function withdrawOrder(Order $order) : OrderResult;

    public function withdrawCallback(Order $order) : OrderResult;
}
