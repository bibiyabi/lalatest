<?php

namespace App\Contracts\Payments;

use App\Models\Order;

interface DepositGatewayInterface
{
    public function withdrawOrder(Order $order) : OrderRs;

    public function withdrawCallback(Order $order) : OrderRs;
}
