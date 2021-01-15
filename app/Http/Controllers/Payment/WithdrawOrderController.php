<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Payments\PaymentInterface;
use App\Exceptions\WithdrawException;
use App\Models\WithdrawOrder;
use App\Repositories\KeyRepository;
use App\Repositories\Orders\WithdrawRepository;
use Illuminate\Support\Facades\Log;
use App\Services\AbstractWithdrawGateway;

class WithdrawOrderController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {

        $this->request = $request->post();
        /*
        $this->request = [];
        $this->request['payment_type'] = 2; // 1 銀行卡 2 電子錢包 3 數字貨幣
        $this->request['user_pk'] = 876;
        $this->request['user_id'] = 1; // 可以先寫死
        $this->request['merchant_name'] = 'java'; // 可以先寫死, 前端要先傳
        $this->request['rate_amount'] = 10;

        $this->request['order_id'] = 123456;
        $this->request['payment_address'] = 'eoifjeoijfoie';
        $this->request['user_phone'] = '123456';

        // 銀行卡
        //bankAddress 所在地区名称
        //bankAddress

        $this->request['bank_user_name'] = 'colin';
        $this->request['bank_code'] = 123456;
        $this->request['phone_number'] = 123456;
        $this->request['user_email'] = 123456; // 用戶郵箱
        $this->request['bank_ifsc'] = ''; // 分行代碼
        */

        Log::channel('withdraw')->info(__LINE__ , $request->post());


        $this->request['user_id'] = $request->user()->id;
        $payment->checkInputData($this->request)->createToQueue();
    }

    public function callback(Request $request, PaymentInterface $payment, AbstractWithdrawGateway $gateway, WithdrawRepository $withdrawRepository) {

        $post = [];
        $post['post'] = $request->post();
        $post['headers'] = getallheaders();
        $res = $payment->callback($post, $gateway);
        $orderId = data_get($res, 'data.order_id');
        $orderId = '1234560000160001708d1e9e';
        $withdrawRepository->filterOrderId($orderId)->update(['status'=> $res->get('code')]);
        $order = $withdrawRepository->filterOrderId($orderId)->first();
        if (empty($order)) {
            throw new WithdrawException('order class not found');
        }
        $payment->callbackNotifyToQueue($order);
        echo $res->get('msg');
    }
}
