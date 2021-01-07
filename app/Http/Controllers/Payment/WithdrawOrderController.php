<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Payments\PaymentInterface;
use App\Services\AbstractWithdrawGateway;

class WithdrawOrderController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {

        $payment->checkInputData($request->post())->toOrderQueue();
    }

    public function cancelOrder() {

    }
}
