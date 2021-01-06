<?php

namespace App\Contracts\Payments;

use App\Models\Order;

interface DepositGatewayInterface
{
    public function genDepositParam(Order $order) : HttpParam;

    public function processOrderResult($rs);

    public function depositCallback(Order $order) : OrderResult;
}
