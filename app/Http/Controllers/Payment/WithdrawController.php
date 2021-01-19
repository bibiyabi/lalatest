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


class WithdrawController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {

        try {
            Log::channel('Withdraw')->info(__LINE__ , $request->post());

            $payment->checkInputData($request)->setOrderToDb()->dispatchOrderQueue();

            return RB::success();
        } catch (Exception $e) {
            Log::channel('Withdraw')->info(__LINE__ , [$e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()]);
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }

    }

    public function callback(Request $request, PaymentInterface $payment, AbstractWithdrawGateway $gateway, WithdrawRepository $withdrawRepository) {





        try {
            Log::channel('Withdraw')->info(__LINE__ , [$request->post(), $request->headers]);

            $res = $payment->callback($request, $gateway);
            $orderId = data_get($res, 'data.order_id');

            $withdrawRepository->filterOrderId($orderId)->update(['status'=> $res->get('code')]);
            $order = $withdrawRepository->filterOrderId($orderId)->first();

            if (empty($order)) {
                throw new WithdrawException('order not found in repository' . json_encode($request->post()), ResponseCode::RESOURCE_NOT_FOUND);
            }
            $payment->callbackNotifyToQueue($order);

            echo $res->get('msg');
        } catch (Exception $e) {
            Log::channel('Withdraw')->info(__LINE__ , [$e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile()]);
            return RB::asError(ResponseCode::EXCEPTION)->withMessage($e->getMessage())->build();
        }
    }
}
