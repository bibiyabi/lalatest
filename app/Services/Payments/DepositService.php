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
use App\Contracts\ResponseCode;
use App\Contracts\Payments\Deposit\DepositGatewayFactory;
use App\Contracts\Payments\Results\ResultFactory;
use Illuminate\Http\Client\Response;

class DepositService
{
    public function order(Request $request): OrderResult
    {
        # create order param
        $user = $request->user();
        $keyId = $request->post('key_id');
        $key = Key::where('user_id', $user->id)
                    ->where('user_pk', $keyId)
                    ->first();

        if (empty($key)) {
            return new OrderResult(false, 'Key not found', ResponseCode::RESOURCE_NOT_FOUND);
        }

        $order_param = $request->post();
        unset($order_param['order_id'], $order_param['key_id'], $order_param['amount']);
        $order = Order::create([
            'order_id' => $request->post('order_id'),
            'user_id'  => $user->id,
            'key_id'   => $keyId,
            'amount'   => $request->post('amount'),
            'gateway_id' => $key->gateway_id,
            'status'   => Status::PENDING,
            'order_param' => json_encode($order_param),
        ]);

        $gateway = DepositGatewayFactory::createGateway($key->gateway->name);
        $param = $gateway->genDepositParam($order);

        # deside how to return value
        $type = $gateway->getReturnType();
        $factory = ResultFactory::createResultFactory($type);

        # submit param
        $unprocessRs = $factory->getResult($param);

        # trigger event ?

        # return result
        $result = $gateway->processOrderResult($unprocessRs);
        return new OrderResult(true, 'Success.', ResponseCode::SUCCESS, $result);
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
