<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Jobs\Payment\Withdraw\Order;

class WithdrawController extends Controller
{
    public function setOrderToProcessing(Request $request) {
        #set db
        $this->dispatch(new Order($request));

        echo 'endOrder';
    }

    public function cancelOrder() {

    }

    public function addConfig() {
        echo '@@@';
    }
}
