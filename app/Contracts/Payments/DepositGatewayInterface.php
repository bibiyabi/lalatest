<?php

namespace App\Contracts\Payments;

use App\Models\Order;

interface DepositGatewayInterface
{
    public function genDepositOrderParam(Order $order) : OrderParam;

    public function processOrderResult($rs);

    public function depositCallback(Order $order) : OrderResult;
}
