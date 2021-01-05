<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Jobs\Payment\Withdraw\Order;
use Illuminate\Support\Facades\Log;
use App\Services\AbstractDepositPayment;
use Illuminate\Support\Facades\Bus;
use Throwable;

class WithdrawController extends Controller
{
    public function create(Request $request) {


        Log::channel('withdraw')->info('User failed to login.', $request->post());

        #set db
        Bus::chain([
            new Order($request->post()),
        ])->catch(function (Throwable $e) {
            Log::channel('withdraw')->error($e->getMessage());
        })->dispatch();


        echo 'endOrder';
    }


    public function cancelOrder() {

    }


}
