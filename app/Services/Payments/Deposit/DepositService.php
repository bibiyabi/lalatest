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
use App\Exceptions\StatusLockedException;
use App\Jobs\Payment\Deposit\Notify;
use App\Repositories\Orders\DepositRepository;
use Auth;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class DepositService
{
    private $orderRepo;

    public function __construct(DepositRepository $orderRepo) {
        $this->orderRepo = $orderRepo;
    }

    public function create(array $input): OrderResult
    {
        # create order param
        $user = Auth::user();
        $keyId = $input['pk'];

        $key = Setting::where('user_id', $user->id)->where('user_pk', $keyId)->first(); //TODO:改用 SettingRepo
        if (empty($key)) {
            return new OrderResult(false, 'Key not found', ResponseCode::RESOURCE_NOT_FOUND);
        }

        # decide how to return value
        try {
            $gateway = DepositGatewayFactory::createGateway($key->gateway->name);
            $type = $gateway->getReturnType();
        } catch (\App\Exceptions\GatewayNotFountException $e) {
            return new OrderResult(false, 'Gateway not found.', ResponseCode::GATEWAY_NOT_FOUND);
        } catch (\ErrorException $e) {
            return new OrderResult(false, 'Key setting error.', ResponseCode::GATEWAY_NOT_FOUND);
        }

        try {
            $temp = $key->gateway_id;
            $order = $this->orderRepo->create($input, $user->id, $key->id, $temp);
        } catch (\PDOException $e) {
            return new OrderResult(false, 'Duplicate OrderId.', ResponseCode::DUPLICATE_ORDERID);
        }

        # submit param
        $param = $gateway->genDepositParam($order);
        $result = ResultFactory::createResultFactory($type)->getResult($param);

        # return result
        $processedResult = $gateway->processOrderResult($result->getContent());
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
        } catch (StatusLockedException $e) {
            return new CallbackResult(true, $e->getMessage());
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
        if ($order->no_notify === false) {
            Notify::dispatch($order);
        }

        return $result;
    }
}
