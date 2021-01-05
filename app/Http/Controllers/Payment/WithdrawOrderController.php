<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Jobs\Payment\Withdraw\Order;
use Illuminate\Support\Facades\Log;
use App\Services\AbstractDepositPayment;
use Illuminate\Support\Facades\Bus;
use Throwable;

class WithdrawOrderController extends Controller
{
    public function create(Request $request) {


        Log::channel('withdraw')->info(__LINE__ , $request->post());
        echo __LINE__ ."\r\n";
        #set db
        Bus::chain([
            new Order($request->post()),
        ])->catch(function (Throwable $e) {
            echo $e->getMessage() . __LINE__ . "\r\n";

        })->dispatch();

        echo __LINE__ ."\r\n";
        echo 'endOrder';
    }


    public function cancelOrder() {

    }


}
