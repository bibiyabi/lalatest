<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Payments\PaymentInterface;
use App\Exceptions\WithdrawException;
use Illuminate\Support\Facades\Log;
use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Lib\Log\LogLine;
use Throwable;

class WithdrawController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {
        try {
            Log::channel('withdraw')->info(new LogLine('代付前端參數'), $request->post());
            $payment->checkInputSetDbSendOrderToQueue($request);
            return RB::success();
        } catch (Throwable $e) {
            Log::channel('withdraw')->info(new LogLine($e));
            return $this->response($e);
        }

    }

    public function callback(Request $request, PaymentInterface $payment, AbstractWithdrawGateway $gateway) {
        try {
            Log::channel('withdraw')->info(new LogLine('代付回調前端參數 post '. print_r($request->post(), true)));
            Log::channel('withdraw')->info(new LogLine('代付回調前端參數 headers'), \Request::header());
            Log::channel('withdraw')->info(new LogLine('代付回調前端參數 php://input ' . print_r(file_get_contents("php://input"), true)));

            $res = $payment->callback($request, $gateway);

            Log::channel('withdraw')->info(new LogLine('callback 回應'), [
                'isSuccess' => $res->getSuccess(),
                'msg' => $res->getMsg(),
            ]);

            $payment->setCallbackDbResult($res);

            return $res->getMsg();

        } catch (Throwable $e) {
            Log::channel('withdraw')->info(new LogLine($e));
            return $this->response($e);
        }
    }


    public function reset(Request $request, PaymentInterface $payment) {
        try {
            Log::channel('withdraw')->info(new LogLine('重置訂單'), $request->post());
            $payment->resetOrderStatus($request);
            return RB::success();
        } catch (Throwable $e) {
            Log::channel('withdraw')->info(new LogLine($e));
            return $this->response($e);
        }
    }

    private function response($e){
        $code = ($e->getCode() < 20 || $e->getCode() > 1024) ? 1024 : $e->getCode();
        if ($e instanceof WithdrawException) {
            return RB::asError($code)->withMessage($e->getMessage())->build();
        }
        return RB::error($code);
    }


}
