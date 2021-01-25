<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Repositories\Orders\DepositRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Services\Payments\Deposit\DepositService;
use DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    private $depositRepo;

    public function __construct(DepositRepository $depositRepo) {
        $this->depositRepo = $depositRepo;
    }

    public function create(Request $request)
    {
        Log::info('Deposit-create', $request->post());

        $validated = $request->validate([
            'order_id' => 'required',
            'pk'   => 'required',
            'amount'   => 'required|numeric|min:0'
        ]);

        $service = App::make(DepositService::class);
        $rs = App::call([$service, 'create'], ['input' => $validated]);

        return $rs->getSuccess()
            ? RB::success($rs->getResult())
            : RB::error($rs->getErrorCode());
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
        $validated = $request->validate([
            'order_id' => 'required'
        ]);

        Log::info('Deposit-reset order_id:' . $request->post('order_id'));

        $this->depositRepo->orderId($validated['order_id'])->reset();

        return RB::success();
    }
}
