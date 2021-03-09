<?php
namespace App\Services\Payments\Withdraw;

use App\Exceptions\WithdrawException;
use App\Jobs\Payment\Withdraw\Order;
use App\Contracts\Payments\PaymentInterface;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Validator;
use App\Jobs\Payment\Withdraw\Notify;
use App\Services\AbstractWithdrawGateway;
use App\Repositories\Orders\WithdrawRepository;
use Illuminate\Http\Request;
use App\Constants\Payments\ResponseCode;
use App\Models\WithdrawOrder;
use App\Contracts\LogLine;
use App\Contracts\Payments\CallbackResult;
use Exception;
use App\Constants\Payments\Status;


class PaymentService implements PaymentInterface
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

    public function callbackNotifyToQueue($order, $message) {
        try {
            Notify::dispatch($order, $message)->onQueue('notify');
        } catch(Throwable $e) {
            throw new WithdrawException($e->getFile(). $e->getLine() .$e->getMessage() , ResponseCode::EXCEPTION);
        }
    }

    public function callback(Request $request , AbstractWithdrawGateway $gateway): CallbackResult {
        return $gateway->callback($request);
    }

    public function resetOrderStatus(Request $request) {

        $post = $request->post();

        $order = WithdrawOrder::where('order_id', $post['order_id'])->first();
        if (empty($order)) {
            throw new WithdrawException("Order not found.");
        }

        $status = WithdrawOrder::where('order_id', $post['order_id'])->update(['no_notify' => 1]);

        if (!$status) {
            throw new WithdrawException("update failed");
        }
    }

    public function setCallbackDbResult(CallbackResult $res) {

        $orderId = $res->getOrder()->order_id;

        if (empty($orderId)) {
            throw new WithdrawException('order id not found in repository', ResponseCode::RESOURCE_NOT_FOUND);
        }

        $callbackStatus =  $res->getSuccess() ? Status::CALLBACK_SUCCESS : Status::CALLBACK_FAILED;

        WithdrawOrder::where('order_id', '=', $orderId)->update(
            [
                'status'=> $callbackStatus,
                'real_amount' => $res->getAmount(),
            ]
        );

        $this->callbackNotifyToQueue($res->getOrder(), $res->getNotifyMessage());

    }

    private function dispatchOrderQueue(Request $request, WithdrawOrder $order)  {
        Order::dispatch($request->post(), $order)->onQueue('withdrawOrder');
    }

    private function checkInputData(Request $request)  {
        $this->validatePost($request->post());
        $this->defaultOrderParams($request->post());
    }

    private function validatePost($post) {
        $validator = Validator::make($post, [
            'type' => 'required',
            'order_id'     => 'required',
            'pk'           => 'required',
        ]);

        if ($validator->fails()) {
            throw new WithdrawException(json_encode($validator->errors()->all()), ResponseCode::ERROR_PARAMETERS);
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

    /**
     * /api/withdraw/create 同步API文件
     *
     * @return void
     */
    private function getNeedDefaultValueParams() {
        return  [
            'amount',
            'bank_card_option',
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
            'network',
            'withdraw_address',
            'gateway_code',
            'ifsc',
            'zip',
            'bank_name',
            'upi_id'
        ];
    }

    private function setOrderToDb(Request $request) {

        $settings = $this->settingRepository->filterCombinePk($request->user()->id, $request->pk)->first();
        $this->settings = collect($settings);

        if (! $this->settings->has('id')) {
            throw new WithdrawException('setting not found, pk' . $request->pk . ' user:'. $request->user()->id , ResponseCode::RESOURCE_NOT_FOUND);
        }

        $order = $this->withdrawRepository->create($request, $settings);

        return $order;
    }




}
