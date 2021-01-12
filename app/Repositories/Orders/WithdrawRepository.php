<?php

namespace App\Repositories\Orders;

use App\Models\WithdrawOrder;

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

    public function filterOrderId($id) {
        $this->order->where('order_id', '=', $id);
        return $this;
    }

}
