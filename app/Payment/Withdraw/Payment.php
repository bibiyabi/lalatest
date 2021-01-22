<?php
namespace App\Payment\Withdraw;

use App\Exceptions\WithdrawException;
use App\Jobs\Payment\Withdraw\Order;
use App\Contracts\Payments\PaymentInterface;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Throwable;

use Illuminate\Support\Facades\Validator;
use App\Jobs\Payment\Withdraw\Notify;
use App\Services\AbstractWithdrawGateway;
use App\Repositories\Orders\WithdrawRepository;
use Illuminate\Http\Request;
use App\Constants\Payments\ResponseCode;
use App\Models\WithdrawOrder;
use App\Contracts\Payments\LogLine;

class Payment implements PaymentInterface
{
    private $settingRepository;
    private $settings;
    private $withdrawRepository;

    public function __construct(WithdrawRepository $withdrawRepository, SettingRepository $settingRepository)
    {
        $this->withdrawRepository = $withdrawRepository;
        $this->settingRepository = $settingRepository;
    }

    public function checkInputSetDbSendOrderToQueue(Request $request) {
        $this->checkInputData($request);
        $order = $this->setOrderToDb($request);
        $this->dispatchOrderQueue($request, $order);
    }

    private function checkInputData(Request $request)  {
        $this->validatePost($request->post());
        $this->defaultOrderParams($request->post());
        return $this;
    }

    private function validatePost($post) {
        $validator = Validator::make($post, [
            'payment_type' => 'required',
            'order_id'     => 'required',
            'pk'           => 'required',
        ]);

        if ($validator->fails()) {
            throw new WithdrawException($validator->fails(), ResponseCode::ERROR_PARAMETERS);
        }
    }

    private function defaultOrderParams($data) {
        $defaultArrays = $this->getNeedDefaultValueParams();
        foreach ($defaultArrays as $key) {
            if (!isset($data[$key])) {
                $data[$key] = '';
            }
        }
        return $data;
    }

    private function getNeedDefaultValueParams() {
        return  [
            'amount',
            'fund_passwd',
            'email',
            'user_country',
            'user_state',
            'user_city',
            'user_address',
            'bank_province',
            'bank_city',
            'bank_address',
            'last_name',
            'first_name',
            'mobile',
            'telegram',
            'withdraw_address',
            'gateway_code',
            'ifsc',
        ];
    }

    private function setOrderToDb(Request $request) {

        $settings = $this->settingRepository->filterCombinePk($request->user()->id, $request->pk)->first();
        $this->settings = collect($settings);

        if (! $this->settings->has('id')) {
            throw new WithdrawException('setting not found, pk' . $request->pk , ResponseCode::RESOURCE_NOT_FOUND);
        }

        $order = $this->withdrawRepository->create($request, $settings);

        return $order;
    }



    private function dispatchOrderQueue(Request $request, WithdrawOrder $order)  {

        Bus::chain([
            new Order($request->post(), $order),
            new Notify($order),
        ])->catch(function (Throwable $e) {
            throw new WithdrawException($e->getFile(). $e->getLine() . $e->getMessage() , ResponseCode::EXCEPTION);
        })->dispatch();

    }

    public function callbackNotifyToQueue($order) {
        try {
            Notify::dispatch($order);
        } catch(Throwable $e) {
            throw new WithdrawException($e->getFile(). $e->getLine() .$e->getMessage() , ResponseCode::EXCEPTION);
        }
    }

    public function callback(Request $request , AbstractWithdrawGateway $gateway) {
        return $gateway->callback($request);
    }

    public function resetOrderStatus(Request $request) {

        $post = $request->post();

        $post['order_id'] = 'colintest3016028';

        $order = WithdrawOrder::where('order_id', $post['order_id'])->first();
        if (empty($order)) {
            throw new WithdrawException("Order not found.");
        }

        Log::channel('withdraw')->info(new LogLine('重置訂單前狀態'), [$order]);

        WithdrawOrder::where('order_id', $post['order_id'])->update(['status' => 2]);

    }



}
