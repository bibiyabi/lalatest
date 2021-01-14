<?php

namespace App\Services\Payments\Deposit;

use App\Contracts\Payments\CallbackResult;
use App\Contracts\Payments\OrderResult;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Constants\Payments\ResponseCode;
use App\Contracts\Payments\Deposit\DepositGatewayFactory;
use App\Contracts\Payments\Results\ResultFactory;
use App\Constants\Payments\Status;
use App\Jobs\Payment\Deposit\Notify;
use App\Models\Order;
use App\Repositories\Orders\DepositRepository;
use Log;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

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
        $key = Setting::where('user_id', $user->id)->where('user_pk', $keyId)->first();

        if (empty($key)) {
            return new OrderResult(false, 'Key not found', ResponseCode::RESOURCE_NOT_FOUND);
        }

        try {
            $order = $this->orderRepo->create($request, $key->gateway_id);
        } catch (\PDOException $e) {
            return new OrderResult(false, 'Duplicate OrderId.', ResponseCode::DUPLICATE_ORDERID);
        }

        # decide how to return value
        try {
            $gateway = DepositGatewayFactory::createGateway($key->gateway->name);
            $type = $gateway->getReturnType();
        } catch (\App\Exceptions\GatewayNotFountException $e) {
            return new OrderResult(false, 'Gateway not found.', ResponseCode::GATEWAY_NOT_FOUND);
        }

        # submit param
        $factory = ResultFactory::createResultFactory($type);
        $param = $gateway->genDepositParam($order);
        $result = $factory->getResult($param);

        # return result
        $processedResult = $gateway->processOrderResult($result);
        $result->setContent($processedResult);
        return new OrderResult(true, 'Success.', ResponseCode::SUCCESS, $result->toArray());
    }

    public function search()
    {
        # code...
    }

    public function callback(Request $request, $gatewayName) : CallbackResult
    {
        # process request
        $gateway = DepositGatewayFactory::createGateway($gatewayName);

        # if failed return false
        try {
            $result = $gateway->depositCallback($request);
        } catch (NotFoundResourceException $e) {
            return new CallbackResult(false, $e->getMessage());
        }

        # update order
        $order = $result->getOrder();
        if ($result->getSuccess()) {
            $order->update([
                'real_amount' => $result->getAmount(),
                'status' => \App\Constants\Payments\Status::CALLBACK_SUCCESS,
            ]);
        } else {
            $order->update([
                'status' => Status::CALLBACK_FAILED,
            ]);
        }

        # push to queue
        Notify::dispatch($order);

        return $result;
    }
}
