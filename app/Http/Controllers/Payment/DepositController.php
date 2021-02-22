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
    private $depositRepo;

    public function __construct(DepositRepository $depositRepo) {
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

        $service = App::make(DepositService::class);
        $rs = App::call([$service, 'create'], ['input' => $request->post()]);

        Log::info('Deposit-result', $rs->getResult());
        return $rs->getSuccess()
            ? RB::success($rs->getResult())
            : RB::error(ResponseCode::EXCEPTION);
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
        $user = $request->user();

        Log::info('Deposit-reset order_id:' . $validated['order_id'] . ' user:' . $user->id);
        $order = $this->depositRepo->user($user->id)->orderId($validated['order_id'])->first();

        if (empty($order)) {
            return RB::error(ResponseCode::RESOURCE_NOT_FOUND);
        }

        return $order->delete()
            ? RB::success()
            : RB::error(ResponseCode::EXCEPTION);
    }
}
