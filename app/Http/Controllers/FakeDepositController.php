<?php

namespace App\Http\Controllers;

use App\Constants\Payments\Status;
use App\Exceptions\NotifyException;
use App\Models\Order;
use App\Models\WithdrawOrder;
use App\Services\Payments\Deposit\DepositNotify;
use App\Services\Payments\PlatformNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FakeDepositController extends Controller
{
    private $service;
    private $withdrawService;

    public function __construct(DepositNotify $service, PlatformNotify $withdrawService) {
        $this->service = $service;
        $this->withdrawService = $withdrawService;
    }

    public function index()
    {
        $minDate = Carbon::now()->subDays(5);
        $orders = Order::where('created_at', '>=', $minDate)->get();
        $url = '/callback/fake_deposit/orders/';
        return view('fake_tparty.order_notify', compact('orders', 'url'));
    }

    public function sendNotify(Order $order, Request $request)
    {
        $this->send($order, $request, $this->service);
    }


    public function withdrawSendNotify(WithdrawOrder $order, Request $request)
    {
        $this->withdrawService->setOrder($order);
        $this->send($order, $request, $this->withdrawService);
    }

    private function send($order, Request $request, $service) {
        if (config('app.env') === 'vip') {
            return response(['success'=>false]);
        }

        $validated = $request->validate([
            'success' => 'required|boolean'
        ]);

        $order->status = $validated['success']
            ? Status::CALLBACK_SUCCESS
            : Status::CALLBACK_FAILED;

        $order->real_amount = $order->amount;

        try {
            $service->notify($order);
        } catch (NotifyException $e) {
            return response(['success'=>false]);
        }

        return response(['success'=>true]);
    }
}
