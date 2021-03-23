<?php

namespace App\Repositories\Orders;

use App\Models\Order;
use App\Constants\Payments\Status;
use App\Models\Setting;
use DateTime;
use Illuminate\Http\Request;

class DepositRepository
{
    private $order;

    public function __construct()
    {
        $this->order = Order::query();
    }

    public function create($order_param, $userId, $keyId, $gatewayId): Order
    {
        $orderId = $order_param['order_id'];
        $amount = $order_param['amount'];
        unset($order_param['key_id']);

        return Order::create([
            'order_id' => $orderId,
            'user_id'  => $userId,
            'key_id'   => $keyId,
            'amount'   => $amount,
            'gateway_id' => $gatewayId,
            'status'   => Status::PENDING,
            'order_param' => json_encode($order_param),
        ]);
    }

    public function first()
    {
        return $this->order->first();
    }

    public function reset(): bool
    {
        return $this->order->update(['no_notify'=> true]);
    }

    public function delete(): bool
    {
        return $this->order->delete();
    }

    public function orderId($orderId): DepositRepository
    {
        $this->order = $this->order->where('order_id', $orderId);
        return $this;
    }

    public function before(DateTime $time): DepositRepository
    {
        $this->order = $this->order->where('created_at', '<', $time);
        return $this;
    }

    public function user(int $userId)
    {
        $this->order = $this->order->where('user_id', $userId);
        return $this;
    }
}
