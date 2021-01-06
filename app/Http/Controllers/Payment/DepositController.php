<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Payments\DepositService;
use App\Contracts\Payments\DepositGatewayInterface;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class DepositController extends Controller
{
    private $service;

    public function __construct() {
        $this->service = new DepositService();
    }

    public function order(Request $request, DepositGatewayInterface $paymentGateway)
    {
        \Log::info('Deposit-order', $request);

        $request->validate([
            'order_id' => 'required',
            'key_id'   => 'required',
            'amount'   => 'required|numeric|min:0'
        ]);

        $rs = $this->service->order($request, $paymentGateway);

        return RB::success($rs);
    }

    public function callback(Request $request, DepositGatewayInterface $gateway)
    {
        \Log::info('Deposit-callback', $request);

        $rs = $this->service->callback($request, $gateway);

        return $rs->getMsg();
    }
}
