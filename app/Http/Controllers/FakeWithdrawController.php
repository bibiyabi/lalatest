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

class FakeWithdrawController extends Controller
{
    private $service;

    public function __construct(PlatformNotify $service) {
        $this->service = $service;
    }

    public function index()
    {
        $minDate = Carbon::now()->subDays(5);
        $orders = WithdrawOrder::where('created_at', '>=', $minDate)->get();
        $url = '/callback/fake_withdraw/orders/';
        return view('fake_tparty.order_notify', compact('orders','url'));
    }

    public function sendNotify(WithdrawOrder $order, Request $request)
    {
        $order->real_amount = $order->amount;

        $this->service->setOrder($order);
        if (config('app.env') === 'vip') {
            return response(['success'=>false]);
        }

        $validated = $request->validate([
            'success' => 'required|boolean'
        ]);

        $status = $validated['success']
            ? PlatformNotify::SUCCESS
            : PlatformNotify::FAIL;

        try {
            $this->service->notify($status);
        } catch (NotifyException $e) {
            return response(['success'=>false]);
        } finally {
            return response(['success'=>false]);
        }

        return response(['success'=>true]);
    }
}
