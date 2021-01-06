<?php

namespace App\Services\Payments;

use App\Contracts\Payments\DepositGatewayInterface;
use App\Contracts\Payments\OrderRs;
use App\Models\Order;
use Illuminate\Http\Request;

class DepositService
{
    public function order(Request $request, DepositGatewayInterface $gateway)
    {
        # create order param
        $order = Order::create($request->post());
        $param = $gateway->genDepositOrderParam($order);

        # submit param

        # process result

        # trigger event ?

        # return result
        return ;
    }

    public function search()
    {
        # code...
    }

    public function callback(Request $request, DepositGatewayInterface $gateway) : OrderRs
    {
        # process request

        # if failed return false

        # update order

        # trigger event

        return new OrderRs();
    }
}
