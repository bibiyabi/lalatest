<?php

namespace App\Http\Controllers;

use App\Constants\Payments\Status;
use App\Exceptions\NotifyException;
use App\Models\Order;
use App\Services\Payments\Deposit\DepositNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FakeDepositController extends Controller
{
    private $service;

    public function __construct(DepositNotify $service) {
        $this->service = $service;
    }

    public function index()
    {
        $minDate = Carbon::now()->subDays(5);
        $orders = Order::where('created_at', '>=', $minDate)->get();

        return view('fake_tparty.deposit', compact('orders'));
    }

    public function sendNotify(Order $order, Request $request)
    {
        if (config('app.env') === 'vip') {
            return response(['success'=>false]);
        }

        $validated = $request->validate([
            'success' => 'required|boolean'
        ]);

        $order->status = $validated['success']
            ? Status::CALLBACK_SUCCESS
            : Status::CALLBACK_FAILED;

        try {
            $this->service->notify($order);
        } catch (NotifyException $e) {
            return response(['success'=>false]);
        }

        return response(['success'=>true]);
    }
}
