<?php

namespace App\Contracts\Payments\Deposit;

use App\Contracts\Payments\CallbackResult;
use App\Models\Order;
use App\Contracts\Payments\HttpParam;
use App\Models\Key;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

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

    public function depositCallback(Request $request): CallbackResult
    {
        $data = $request->all();
        $status = isset($data[$this->getCallbackKeyStatus()])  ? (bool)$data[$this->getCallbackKeyStatus()] : false;

        if (!isset($data[$this->getCallbackKeyOrderId()])) {
            throw new NotFoundResourceException("OrderId not found.");
        }

        $order = Order::where('order_id', $data[$this->getCallbackKeyOrderId()])->first();
        if (empty($order)) {
            throw new NotFoundResourceException("Order not found.");
        }

        $key = $order->key;
        if (empty($key)) {
            throw new NotFoundResourceException("Order not found.");
        }

        if (
            !isset($data[$this->getCallbackKeySign()])
            || $data[$this->getCallbackKeySign()] != $this->createCallbackSign($data, $key)
        ) {
            throw new NotFoundResourceException("Sign error.");
        }

        if ($status === false) {
            return new CallbackResult(false, $this->getCallbackSuccessReturn(), $order);
        }

        return new CallbackResult(true, $this->getCallbackSuccessReturn(), $order, $data[$this->getCallbackKeyAmount()]);
    }
}
