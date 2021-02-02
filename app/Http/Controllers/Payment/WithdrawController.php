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
            if ($e instanceof WithdrawException) {
                return RB::asError($e->getCode())->withMessage($e->getMessage())->build();
            }
            return RB::error($e->getCode());
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

            $payment->setCallbackDbResult($res);

            return $res->getMsg();

        } catch (Throwable $e) {
            Log::channel('withdraw')->info(new LogLine($e));
            if ($e instanceof WithdrawException) {
                return RB::asError($e->getCode())->withMessage($e->getMessage())->build();
            }
            return RB::error($e->getCode());
        }
    }


    public function reset(Request $request, PaymentInterface $payment) {
        try {
            Log::channel('withdraw')->info(new LogLine('重置訂單'), $request->post());
            $payment->resetOrderStatus($request);
            return RB::success();
        } catch (Throwable $e) {
            Log::channel('withdraw')->info(new LogLine($e));
            if ($e instanceof WithdrawException) {
                return RB::asError($e->getCode())->withMessage($e->getMessage())->build();
            }
            return RB::error($e->getCode());
        }
    }
}
