<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Services\Payments\DepositService;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
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

        $success = $rs->getSuccess();

        return $success
            ? RB::success()
            : RB::error($rs->getErrorCode());
    }

    public function callback(Request $request, DepositGatewayInterface $gateway)
    {
        \Log::info('Deposit-callback', $request);

        $rs = $this->service->callback($request, $gateway);

        return $rs->getMsg();
    }
}
