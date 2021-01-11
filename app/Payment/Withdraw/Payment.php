<?php
namespace App\Payment\Withdraw;

use App\Exceptions\WithdrawException;
use App\Jobs\Payment\Withdraw\Order;
use App\Contracts\Payments\PaymentInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Throwable;
use App\Models\WithdrawOrder;
use App\Constants\PaymentType;
use Illuminate\Support\Facades\Validator;
use App\Repositories\KeyRepository;
use Illuminate\Support\Facades\DB;
class Payment implements PaymentInterface
{

    private $postData;
    private $keyRepository;
    private $keys;


    public function __construct(KeyRepository $k)
    {
        $this->keyRepository = $k;
    }

    public function checkInputData($postData)  {

        $this->postData = $postData;

        $this->postData['merchant_name'] = 'java';
        $this->postData['user_id'] = '1';



        switch ($this->postData['payment_type']) {
            case PaymentType::BANK:
                $this->check_bank_post();
                break;
            case PaymentType::WALLET:
                $this->check_wallet_post();
                break;
            case PaymentType::DIGITAL_CURRENCY:
                $this->check_digital_currency_post();
                break;
            default:
                throw new WithdrawException("asdsd");
        }

        return $this;
    }

    private function check_bank_post() {

        $validator = Validator::make($this->postData, [

            'payment_type' => 'required',
            'order_id'     => 'required',
            'user_pk'      => 'required',
            'address'      => 'required',
            'rate_amount'  => 'required',

            'bank_code'    => 'required',
            'phone_number' => 'required',
            //'bank_privince' => '', //
            //'bank_area' => '', //
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return "您輸入的資料有誤";
        }
    }

    private function check_wallet_post() {

        $validator = Validator::make($this->postData, [
            'payment_type' => 'required',
            'order_id'     => 'required',
            'user_pk'      => 'required',
            'address'      => 'required',
            'rate_amount'  => 'required',
        ]);

        if ($validator->fails()) {
            return "您輸入的資料有誤";
        }
    }

    private function check_digital_currency_post() {

        $validator = Validator::make($this->postData, [
            'payment_type' => 'required',
            'order_id'     => 'required',
            'user_pk'      => 'required',
            'address'      => 'required',
            'rate_amount'  => 'required',
        ]);

        if ($validator->fails()) {
            return "您輸入的資料有誤";
        }
    }

    private function setOrderToDb() {

        DB::enableQueryLog();
        #$this->postData['user_id'] = 1;
        #$this->postData['user_pk'] = 6;
        $keys = $this->keyRepository->filterCombinePk($this->postData['user_id'], $this->postData['user_pk'])->first();

        $this->keys = collect($keys);

        if (! $this->keys->has('id')  ) {
            throw new WithdrawException('aaa', 0 );
        }

        WithdrawOrder::create([
            'order_id'    => (string) $this->postData['order_id']. uniqid(),
            'user_id'     => $this->postData['user_id'],
            'key_id'      => $this->keys->get('id'),
            'amount'      => $this->postData['rate_amount'],
            'real_amount' => $this->postData['rate_amount'],
            'gateway_id'  => $this->keys->get('gateway_id'),
            'status'      => 1,
            'order_param' => json_encode($this->postData, true),
        ]);
    }

    public function prepareToQueue()  {

        $this->setOrderToDb();

        $this->postData['key_id'] = $this->keys->get('id');
        $this->postData['gateway_id'] = $this->keys->get('gateway_id');

        Bus::chain([
            new Order($this->postData),
        ])->catch(function (Throwable $e) {
            echo $e->getMessage() . __LINE__ . "\r\n";

        })->dispatch();

        echo __LINE__ ."\r\n";
        echo 'endOrder';
    }
}
