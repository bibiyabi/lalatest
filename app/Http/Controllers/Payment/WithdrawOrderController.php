<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Payments\PaymentInterface;


use Illuminate\Support\Facades\Log;
use App\Services\AbstractWithdrawGateway;

class WithdrawOrderController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {

        $this->request = [];
        $this->request['payment_type'] = 2; // 1 銀行卡 2 電子錢包 3 數字貨幣
        $this->request['user_pk'] = 6;
        $this->request['user_id'] = 1; // 可以先寫死
        $this->request['merchant_name'] = 'java'; // 可以先寫死, 前端要先傳
        $this->request['rate_amount'] = 10;

        $this->request['order_id'] = 123456;
        $this->request['address'] = 'eoifjeoijfoie';

        // 銀行卡
        $this->request['bank_code'] = 123456;
        $this->request['phone_number'] = 123456;
        $this->request['email'] = 123456; // 用戶郵箱
        $this->request['ifsc'] = ''; // 分行代碼

        Log::channel('withdraw')->info(__LINE__ , $request->post());


        $this->request['user_id'] = 123;
        $payment->checkInputData($this->request)->createToQueue();
    }

    public function callback(Request $request, PaymentInterface $payment, AbstractWithdrawGateway $gateway ) {

        $res = $payment->callback($request->post(), $gateway);

        print_r($res);

        $payment->callbackNotifyToQueue($res);
    }
}
