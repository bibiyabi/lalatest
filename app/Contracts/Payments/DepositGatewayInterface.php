<?php

namespace App\Contracts\Payments;

use App\Models\Order;

interface DepositGatewayInterface
{
    public function depositOrder(Order $order) : OrderRs;

    public function depositCallback(Order $order) : OrderRs;
}
