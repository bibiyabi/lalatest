<?php

namespace App\Repositories\Orders;

use App\Models\Order;
use App\Constants\Payments\Status;
use App\Models\Key;
use Illuminate\Http\Request;

class DepositRepository
{
    public function create(Request $request, $gateway_id): Order
    {
        $user = $request->user();
        $order_param = $request->post();
        $key = Key::where('user_id', $user->id)->where('user_pk', $request->post('key_id'))->first();
        unset($order_param['order_id'], $order_param['key_id'], $order_param['amount']);

        return Order::create([
            'order_id' => $request->post('order_id'),
            'user_id'  => $user->id,
            'key_id'   => $key->id,
            'amount'   => $request->post('amount'),
            'gateway_id' => $gateway_id,
            'status'   => Status::PENDING,
            'order_param' => json_encode($order_param),
        ]);
    }
}
