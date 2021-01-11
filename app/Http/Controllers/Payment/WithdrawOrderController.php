<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Payments\PaymentInterface;
use App\Services\AbstractWithdrawGateway;

class WithdrawOrderController extends Controller
{
    public function create(Request $request, PaymentInterface $payment) {

        $this->request = [];
        $this->request['user_pk'] = 2;
        $this->request['user_id'] = 1;

        $payment->checkInputData( $this->request)->toOrderQueue();
    }

    public function cancelOrder() {

    }
}
