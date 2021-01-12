<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Services\Payments\DepositService;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class DepositController extends Controller
{
    public function order(Request $request)
    {
        \Log::info('Deposit-order', $request->post());

        $request->validate([
            'order_id' => 'required',
            'key_id'   => 'required',
            'amount'   => 'required|numeric|min:0'
        ]);

        $service = App::make(DepositService::class);
        $rs = App::call([$service, 'order'], ['request' => $request]);

        return $rs->getSuccess()
            ? RB::success($rs->getResult())
            : RB::error($rs->getErrorCode());
    }

    public function callback(Request $request, $gatewayName)
    {
        \Log::info('Deposit-callback', compact('gatewayName', 'request'));

        $service = App::make(DepositService::class);
        $rs = App::call([$service, 'callback'], compact('gatewayName', 'request'));

        return $rs->getMsg();
    }
}
