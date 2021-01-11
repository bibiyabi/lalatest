<?php

namespace App\Contracts\Payments\Deposit;

use App\Models\Order;
use App\Contracts\Payments\HttpParam;
use App\Models\Key;

trait DepositGatewayHelper
{
    public function genDepositParam(Order $order): HttpParam
    {
        $param = $this->createParam($order, $order->key);
        $param[$this->getSignKey()] = $this->createSign($param, $order->key);

        return new HttpParam($this->getUrl(), $this->getMethod(), $this->getHeader(), $param, $this->getConfig());
    }

    abstract protected function createParam(Order $order, Key $key): array;

    abstract protected function createSign(array $param, Key $key): string;

    abstract protected function getUrl(): string;

    protected function getHeader(): array
    {
        return [];
    }

    protected function getConfig(): array
    {
        return [];
    }

    protected function getSignKey()
    {
        return 'sign';
    }

    protected function getMethod()
    {
        return 'post';
    }
}
