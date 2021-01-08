<?php

namespace App\Services\Payments;

use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\OrderResult;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Contracts\Payments\Results\UrlResult;
use App\Contracts\Payments\Results\FormResult;
use App\Models\Key;
Use App\Contracts\Payments\Status;

class DepositService
{
    private $gateway;

    public function __construct(DepositGatewayInterface $gateway) {
        $this->gateway = $gateway;
    }

    public function order(Request $request): OrderResult
    {
        # create order param
        $user = $request->user();
        $keyId = $request->post('key_id');
        $key = Key::where('user_id', $user->id)
                    ->where('user_pk', $keyId)
                    ->first();

        if (empty($key)) {
            return new OrderResult(false, 'Key not found', Status::ORDER_FAILED);
        }

        $order = Order::create([
            'order_id' => $request->post('order_id'),
            'user_id'  => $user->id,
            'key_id'   => $keyId,
            'amount'   => $request->post('amount'),
            'gateway_id' => $key->gateway_id,
        ]);
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
