<?php

namespace App\Contracts\Payments\Deposit;

use App\Models\Order;
use App\Contracts\Payments\HttpParam;
use App\Models\Key;

trait DepositGatewayHelper
{
    public function genDepositParam(Order $order): HttpParam
    {
        $key = $order->key();
        $config = json_decode($key->keys);

        $param = $this->createParam($order, $config);
        $param[$this->getSignKey()] = $this->createSign($param, $config);

        return new HttpParam($this->getUrl(), $this->getMethod(), [], $param, []);
    }

    abstract protected function createParam(Order $order, Key $key): array;

    abstract protected function createSign(array $param, Key $key): string;

    abstract protected function getUrl(): string;

    protected function getSignKey()
    {
        return 'sign';
    }

    protected function getMethod()
    {
        return 'post';
    }
}
