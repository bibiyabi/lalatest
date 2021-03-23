<?php

namespace App\Repositories\Orders;

use App\Models\WithdrawOrder;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Constants\Payments\Status;
use DateTime;

class WithdrawRepository
{
    private $order;

    public function __construct()
    {
        $this->order = WithdrawOrder::query();
    }

    public function first()
    {
        return $this->order->first();
    }

    public function get()
    {
        return $this->order->get();
    }

    public function filterOrderId($id)
    {
        $this->order->where('order_id', '=', $id);
        return $this;
    }

    public function update($data = [])
    {
        $this->order->update($data);
    }

    public function create(Request $request, Setting $setting)
    {
        return WithdrawOrder::create([
            'order_id'    => $request->order_id,
            'user_id'     => $request->user()->id,
            'key_id'      => $setting->id,
            'amount'      => $request->amount,
            'gateway_id'  => $setting->gateway_id,
            'status'      => Status::PENDING,
            'order_param' => json_encode($request->post(), true),
        ]);
    }

    public function before(DateTime $time): WithdrawRepository
    {
        $this->order = $this->order->where('created_at', '<', $time);
        return $this;
    }

    public function delete(): bool
    {
        return $this->order->delete();
    }
}
