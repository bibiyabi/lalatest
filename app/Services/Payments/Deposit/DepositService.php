<?php

namespace App\Services\Payments\Deposit;

use App\Contracts\Payments\CallbackResult;
use App\Contracts\Payments\OrderResult;
use Illuminate\Http\Request;
use App\Constants\Payments\ResponseCode;
use App\Contracts\Payments\Deposit\DepositGatewayFactory;
use App\Lib\Payments\Results\ResultFactory;
use App\Constants\Payments\Status;
use App\Exceptions\StatusLockedException;
use App\Exceptions\TpartyException;
use App\Jobs\Payment\Deposit\Notify;
use App\Repositories\GatewayRepository;
use App\Repositories\Orders\DepositRepository;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class DepositService
{
    private $orderRepo;

    private $settingRepo;

    private $gatewayRepo;

    public function __construct(DepositRepository $orderRepo, SettingRepository $settingRepo, GatewayRepository $gatewayRepo)
    {
        $this->orderRepo = $orderRepo;
        $this->settingRepo = $settingRepo;
        $this->gatewayRepo = $gatewayRepo;
    }

    public function create(Request $request): OrderResult
    {
        # get user
        $user = $request->user();
        $setting = $this->settingRepo->filterCombinePk($user->id, $request->post('pk'))->first();
        if ($setting === null) {
            throw new NotFoundResourceException('Setting not found.');
        }

        # get gateway class
        $gatewayModel = $this->gatewayRepo->filterGatewayId($setting->gateway_id)->first();
        if ($gatewayModel === null) {
            throw new NotFoundResourceException("Gateway not found.");
        }
        $gateway = DepositGatewayFactory::createGateway($gatewayModel->name);
        Log::info('Deposit-gateway: ' . $gatewayModel->name);

        # insert order to db
        $order = $this->orderRepo->create(
            $request->post(),
            $user->id,
            $setting->id,
            $setting->gateway_id
        );

        # submit param
        $depositParam = $gateway->genDepositParam($order);
        $result = ResultFactory::createResultFactory($gateway->getReturnType())->getResult($depositParam);
        Log::info('Deposit-Tparty-Result ' . $result->getContent());

        # get url from tparty response or rerutn form directly
        $processedResult = ($gateway->getReturnType() == 'url')
            ? $gateway->processOrderResult($result->getContent())
            : $result->getContent();

        # return result
        $result->setContent($processedResult);
        return new OrderResult(true, 'Success.', ResponseCode::SUCCESS, $result->toArray());
    }

    public function callback(Request $request, $gatewayName) : CallbackResult
    {
        # process request
        $gateway = DepositGatewayFactory::createGateway($gatewayName);

        # if failed return false
        try {
            $result = $gateway->depositCallback($request);
        } catch (TpartyException $e) {
            Log::info('Tparty-exception ' . $e->getMessage());
            return new CallbackResult(false, $e->getMessage());
        } catch (StatusLockedException $e) {
            Log::info('Order already locked.');
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
            Log::info('Deposit-callback-dispatch ' . $order->order_id);
            Notify::dispatch($order)->onQueue('notify');
        }

        return $result;
    }

    public function reset(int $userId, string $orderId)
    {
        return $this->orderRepo->user($userId)->orderId($orderId)->reset();
    }
}
