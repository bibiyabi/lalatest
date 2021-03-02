<?php

namespace App\Http\Controllers\Payment;

use App\Constants\Payments\ResponseCode;
use App\Http\Controllers\Controller;
use App\Repositories\Orders\DepositRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Services\Payments\Deposit\DepositService;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    private $service;

    public function __construct(DepositService $service, DepositRepository $depositRepo) {
        $this->service = $service;
        $this->depositRepo = $depositRepo;
    }

    public function create(Request $request)
    {
        Log::info('Deposit-create', $request->post());

        $request->validate([
            'order_id' => 'required',
            'pk'   => 'required',
            'amount'   => 'required|numeric|min:0'
        ]);

        $rs = $this->service->create($request);

        Log::info('Deposit-result', $rs->getResult());
        return RB::success($rs->getResult());
    }

    public function callback(Request $request, $gatewayName)
    {
        Log::info('Deposit-callback '. $gatewayName, $request->all());

        $service = App::make(DepositService::class);
        $rs = App::call([$service, 'callback'], compact('gatewayName', 'request'));

        return $rs->getMsg();
    }

    public function reset(Request $request)
    {
        $validated = $request->validate(['order_id' => 'required']);

        Log::info('Deposit-reset order_id:' . $validated['order_id']);

        return $this->service->reset($request->user()->id, $validated['order_id'])
            ? RB::success()
            : RB::error(ResponseCode::EXCEPTION);
    }
}
