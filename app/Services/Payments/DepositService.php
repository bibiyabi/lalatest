<?php

namespace App\Services\Payments;

use App\Contracts\Payments\DepositGatewayInterface;
use App\Contracts\Payments\OrderResult;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Contracts\Payments\Results\ResultFactory;

class DepositService
{
    public function order(Request $request, DepositGatewayInterface $gateway, ResultFactory $factory)
    {
        # create order param
        $order = Order::create($request->post());
        $param = $gateway->genDepositOrderParam($order);

        # submit param
        $unprocessRs = $factory->getResult($param);

        # process result
        $rs = $gateway->processOrderResult($unprocessRs);

        # trigger event ?

        # return result
        return $rs;
    }

    public function search()
    {
        # code...
    }

    public function callback(Request $request, DepositGatewayInterface $gateway) : OrderResult
    {
        # process request

        # if failed return false

        # update order

        # trigger event

        return new OrderResult();
    }
}
