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
