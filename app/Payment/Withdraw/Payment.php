<?php
namespace App\Payment\Withdraw;

use App\Exceptions\WithdrawException;
use App\Jobs\Payment\Withdraw\Order;
use App\Contracts\Payments\PaymentInterface;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Throwable;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Validator;
use App\Jobs\Payment\Withdraw\Notify;
use App\Services\AbstractWithdrawGateway;
use App\Repositories\Orders\WithdrawRepository;

class Payment implements PaymentInterface
{

    private $postData;
    private $settingRepository;
    private $settings;
    private $withdrawRepository;


    public function __construct(SettingRepository $k, WithdrawRepository $withdrawRepository)
    {
        $this->settingRepository = $k;
        $this->withdrawRepository = $withdrawRepository;
    }

    public function checkInputData($postData)  {

        var_dump($postData);

        $this->postData = $postData;

        $validator = Validator::make($this->postData, [
            'payment_type' => 'required',
            'order_id'     => 'required',
            'pk'      => 'required',
        ]);

        if ($validator->fails()) {
            throw new WithdrawException('input fail');
        }

        $this->defaultOrderParams($this->postData);

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


    private function setOrderToDb() {

        //DB::enableQueryLog();
        #$this->postData['user_id'] = 1;
        #$this->postData['pk'] = 6;
        $settings = $this->settingRepository->filterCombinePk($this->postData['user_id'], $this->postData['pk'])->first();

        $this->settings = collect($settings);

        if (! $this->settings->has('id')) {
            throw new WithdrawException('setting not has user_id', 0 );
        }

        $this->postData['order_id'] = $this->postData['order_id']. uniqid();

        WithdrawOrder::create([
            'order_id'    => $this->postData['order_id'],
            'user_id'     => $this->postData['user_id'],
            'key_id'      => $this->settings->get('id'),
            'amount'      => $this->postData['amount'],
            'real_amount' => $this->postData['amount'],
            'gateway_id'  => $this->settings->get('gateway_id'),
            'status'      => 1,
            'order_param' => json_encode($this->postData, true),
        ]);
    }


    public function createToQueue()  {

        $this->setOrderToDb();

        $this->postData['key_id'] = $this->settings->get('id');
        $this->postData['gateway_id'] = $this->settings->get('gateway_id');

        $order = $this->withdrawRepository->filterOrderId($this->postData['order_id'])->first();

        Bus::chain([
            new Order($this->postData),
            new Notify($order),
        ])->catch(function (Throwable $e) {
            echo $e->getMessage() . __LINE__ . "\r\n";
        })->dispatch();

        echo __LINE__ ."\r\n";
        echo 'endOrder';
    }

    public function callbackNotifyToQueue($order) {

        Bus::chain([
            new Notify($order),
        ])->catch(function (Throwable $e) {
            echo $e->getMessage() . __LINE__ . "\r\n";

        })->dispatch();
    }

    public function callback($postData , AbstractWithdrawGateway $gateway) {
        $gatewayRes =  $gateway->callback($postData);
        return $gatewayRes;
    }



}
