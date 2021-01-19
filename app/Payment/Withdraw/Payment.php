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

class Payment implements PaymentInterface
{

    private $postData;
    private $settingRepository;
    private $settings;
    private $withdrawRepository;
    private $request;


    public function __construct(WithdrawRepository $withdrawRepository, SettingRepository $settingRepository)
    {
        $this->withdrawRepository = $withdrawRepository;
        $this->settingRepository = $settingRepository;
    }

    public function checkInputData(Request $request)  {

        $this->request = $request;

        $validator = Validator::make($this->request->post(), [
            'payment_type' => 'required',
            'order_id'     => 'required',
            'pk'           => 'required',
        ]);

        if ($validator->fails()) {
            throw new WithdrawException($validator->fails(), ResponseCode::ERROR_PARAMETERS);
        }

        $this->defaultOrderParams($this->request->post());

        return $this;
    }


    private function defaultOrderParams($data) {

        $defaultArrays = [
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

        foreach ($defaultArrays as $key) {
            if (!isset($data[$key])) {
                $data[$key] = '';
            }
        }
        return $data;

    }

    public function setOrderToDb() {

        $settings = $this->settingRepository->filterCombinePk($this->request->user()->id, $this->request->pk)->first();

        $this->settings = collect($settings);

        if (! $this->settings->has('id')) {
            throw new WithdrawException('setting not found, pk' . $this->request->pk , ResponseCode::RESOURCE_NOT_FOUND);
        }

        $this->withdrawRepository->create($this->request, $settings);

        return $this;
    }


    public function dispatchOrderQueue()  {

        $post = $this->request->post();

        $post['key_id'] = $this->settings->get('id');
        $post['gateway_id'] = $this->settings->get('gateway_id');

        $order = $this->withdrawRepository->filterOrderId($this->request->order_id)->first();

        Bus::chain([
            new Order($post),
            new Notify($order),
        ])->catch(function (Throwable $e) {
            throw new WithdrawException($e->getFile(). $e->getLine() . $e->getMessage() , ResponseCode::EXCEPTION);
        })->dispatch();

    }

    public function callbackNotifyToQueue($order) {

        Bus::chain([
            new Notify($order),
        ])->catch(function (Throwable $e) {
            throw new WithdrawException($e->getFile(). $e->getLine() .$e->getMessage() , ResponseCode::EXCEPTION);
        })->dispatch();
    }

    public function callback(Request $request , AbstractWithdrawGateway $gateway) {
        return $gateway->callback($request);
    }



}
