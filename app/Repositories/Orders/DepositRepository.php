<?php

namespace App\Repositories\Orders;

use App\Models\Order;
use App\Constants\Payments\Status;
use App\Models\Setting;
use Illuminate\Http\Request;

class DepositRepository
{
    private $order;

    public function create($order_param, $userId, $keyId, $gatewayId): Order
    {
        $orderId = $order_param['order_id'];
        $amount = $order_param['amount'];
        unset($order_param['order_id'], $order_param['key_id'], $order_param['amount']);

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

    public function reset(): bool
    {
        return $this->order->update(['no_notify'=> true]);
    }

    public function orderId($orderId): DepositRepository
    {
        $this->order = Order::where('order_id', $orderId);
        return $this;
    }
}
