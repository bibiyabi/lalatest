<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Payments\PaymentInterface;

class WithdrawOrderController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {
        $payment->checkInputData($request)->toOrderQueue();
    }

    public function cancelOrder() {

    }
}
