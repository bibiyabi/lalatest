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
use App\Contracts\Payments\LogLine;

class WithdrawController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {
        try {
            Log::channel('withdraw')->info(new LogLine('代付前端參數'), $request->post());
            $payment->checkInputSetDbSendOrderToQueue($request);
            return RB::success();
        } catch (Exception $e) {
            Log::channel('withdraw')->info(new LogLine($e), $request->post());
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }

    }

    public function callback(Request $request, PaymentInterface $payment, AbstractWithdrawGateway $gateway, WithdrawRepository $withdrawRepository) {

        try {
            Log::channel('withdraw')->info(new LogLine('代付回調前端參數'), ['post' => $request->post(), 'header' => $request->headers]);

            $res = $payment->callback($request, $gateway);

            Log::channel('withdraw')->info(new LogLine('callback 回應'), $res);

            $orderId = data_get($res, 'data.order_id');

            if (empty($orderId)) {
                throw new WithdrawException('order id not found in repository', ResponseCode::RESOURCE_NOT_FOUND);
            }

            $withdrawRepository->filterOrderId($orderId)->update(['status'=> $res->get('code')]);
            $order = $withdrawRepository->filterOrderId($orderId)->first();

            if (empty($order)) {
                throw new WithdrawException('order not found in repository', ResponseCode::RESOURCE_NOT_FOUND);
            }

            $payment->callbackNotifyToQueue($order);

            echo $res->get('msg');

        } catch (Exception $e) {
            Log::channel('withdraw')->info(new LogLine($e));
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }
    }


    public function reset(Request $request, PaymentInterface $payment) {
        try {
            Log::channel('withdraw')->info(new LogLine('重置訂單'), $request->post());
            $payment->resetOrderStatus($request);
            return RB::success();
        } catch (Exception $e) {
            Log::channel('withdraw')->info(new LogLine($e), $request->post());
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }
    }
}
