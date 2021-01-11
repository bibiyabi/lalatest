<?php

namespace App\Services\Payments;

use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\OrderResult;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Key;
use App\Contracts\ResponseCode;
use App\Contracts\Payments\Deposit\DepositGatewayFactory;
use App\Contracts\Payments\Results\ResultFactory;
use App\Repositories\Orders\DepositRepository;

class DepositService
{
    private $orderRepo;

    public function __construct(DepositRepository $orderRepo) {
        $this->orderRepo = $orderRepo;
    }

    public function order(Request $request): OrderResult
    {
        # create order param
        $user = $request->user();
        $keyId = $request->post('key_id');
        $key = Key::where('user_id', $user->id)->where('user_pk', $keyId)->first();

        if (empty($key)) {
            return new OrderResult(false, 'Key not found', ResponseCode::RESOURCE_NOT_FOUND);
        }

        try {
            $order = $this->orderRepo->create($request, $key->gateway_id);
        } catch (\PDOException $e) {
            return new OrderResult(false, 'Duplicate OrderId.', ResponseCode::DUPLICATE_ORDERID);
        }

        # deside how to return value
        $gateway = DepositGatewayFactory::createGateway($key->gateway->name);
        $type = $gateway->getReturnType();

        # submit param
        $factory = ResultFactory::createResultFactory($type);
        $param = $gateway->genDepositParam($order);
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
