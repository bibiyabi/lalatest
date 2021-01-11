<?php

namespace App\Repositories;

use App\Models\Gateway;

class GatewayRepository
{
    private $gateway;

    public function __construct()
    {
        $this->gateway = Gateway::query();
    }

    public function first()
    {
        return $this->gateway->first();
    }

    public function get()
    {
        return $this->gateway->get();
    }

    public function filterGatewayId($id) {
        $this->gateway->where('id', '=', $id);
        return $this;
    }

}
