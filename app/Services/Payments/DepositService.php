<?php

namespace App\Services\Payments;

use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\OrderResult;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Contracts\Payments\Results\UrlResult;
use App\Contracts\Payments\Results\FormResult;

class DepositService
{
    private $gateway;

    public function __construct(DepositGatewayInterface $gateway) {
        $this->gateway = $gateway;
    }

    public function order(Request $request)
    {
        # create order param
        $order = Order::create($request->post());
        $param = $this->gateway->genDepositParam($order);

        # deside how to return value
        $type = $this->gateway->getReturnType();
        switch ($type) {
            case 'url':
                $factory = new UrlResult();
                break;

            case 'form':
                $factory = new FormResult();
                break;

            default:
                throw new \Exception("Result factory not found", 1);
                break;
        }

        # submit param
        $unprocessRs = $factory->getResult($param);

        # trigger event ?

        # return result
        return $this->gateway->processOrderResult($unprocessRs);
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
