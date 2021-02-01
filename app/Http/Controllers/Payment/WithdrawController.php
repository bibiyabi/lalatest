<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Payments\PaymentInterface;
use App\Exceptions\WithdrawException;
use App\Repositories\Orders\WithdrawRepository;
use Illuminate\Support\Facades\Log;
use App\Services\AbstractWithdrawGateway;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Exception;
use App\Constants\Payments\ResponseCode;
use App\Contracts\LogLine;
use App\Constants\Payments\Status;
use App\Models\WithdrawOrder;


class WithdrawController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {
        try {
            Log::channel('withdraw')->info(new LogLine('代付前端參數'), $request->post());
            $payment->checkInputSetDbSendOrderToQueue($request);
            return RB::success();
        } catch (WithdrawException $e) {
            Log::channel('withdraw')->info(new LogLine($e), $request->post());
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }

    }

    public function callback(Request $request, PaymentInterface $payment, AbstractWithdrawGateway $gateway) {

        try {
            Log::channel('withdraw')->info(new LogLine('代付回調前端參數'),[
                'post' => $request->post(),
                'header' => \Request::header(),
                'phpinput' => file_get_contents("php://input")
            ]);

            $res = $payment->callback($request, $gateway);

            Log::channel('withdraw')->info(new LogLine('callback 回應'), [
                'isSuccess' => $res->getSuccess(),
                'msg' => $res->getMsg(),
            ]);

            $orderId = $res->getOrder()->order_id;

            if (empty($orderId)) {
                throw new WithdrawException('order id not found in repository', ResponseCode::RESOURCE_NOT_FOUND);
            }

            $callbackStatus =  $res->getSuccess() ? Status::CALLBACK_SUCCESS : Status::CALLBACK_FAILED;

            WithdrawOrder::where('order_id', '=', $orderId)->update(
                [
                    'status'=> $callbackStatus,
                    'real_amount' => $res->getAmount(),
                    'order_param' => json_encode($request->post())
                ]
            );

            $order = WithdrawOrder::where('order_id', '=', $orderId)->first();

            if (empty($order)) {
                throw new WithdrawException('order not found in repository', ResponseCode::RESOURCE_NOT_FOUND);
            }

            $payment->callbackNotifyToQueue($order);

            return $res->getMsg();

        } catch (WithdrawException $e) {
            Log::channel('withdraw')->info(new LogLine($e));
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }
    }


    public function reset(Request $request, PaymentInterface $payment) {
        try {
            Log::channel('withdraw')->info(new LogLine('重置訂單'), $request->post());
            $payment->resetOrderStatus($request);
            return RB::success();
        } catch (WithdrawException $e) {
            Log::channel('withdraw')->info(new LogLine($e), $request->post());
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }
    }
}
