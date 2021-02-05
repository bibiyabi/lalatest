<?php

namespace App\Contracts\Payments\Deposit;

use App\Contracts\Payments\CallbackResult;
use App\Models\Order;
use App\Contracts\Payments\HttpParam;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use App\Constants\Payments\Status;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use App\Exceptions\StatusLockedException;

trait DepositGatewayHelper
{
    public function getDepositHttpMethod(): string
    {
        return $this->method;
    }

    public function getReturnType(): string
    {
        return $this->returnType;
    }

    public function genDepositParam(Order $order): HttpParam
    {
        $settingParam = SettingParam::createFromJson($order->key->settings);
        $orderParam = OrderParam::createFromJson($order->order_param);

        $param = $this->createParam($orderParam, $settingParam);
        $param[$this->getSignKey()] = $this->createSign($param, $settingParam);

        return new HttpParam($this->getUrl(), $this->getMethod(), $this->getHeader(), $param, $this->getConfig());
    }

    abstract protected function createParam(OrderParam $orderParam, SettingParam $settingParam): array;

    abstract protected function createSign(array $param, SettingParam $key): string;

    protected function getUrl(): string
    {
        return $this->url . $this->orderUri;
    }

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
        return $this->method;
    }

    public function depositCallback(Request $request): CallbackResult
    {
        $data = $request->all();
        $status = isset($data[$this->getKeyStatus()]) ? $data[$this->getKeyStatus()] == $this->getKeyStatusSuccess() : true;

        if (!isset($data[$this->getKeyOrderId()])) {
            throw new NotFoundResourceException("OrderId not found.");
        }

        $order = Order::where('order_id', $data[$this->getKeyOrderId()])->first();
        if (empty($order)) {
            throw new NotFoundResourceException("Order not found.");
        }

        $settingParam = SettingParam::createFromJson($order->key->settings);
        if (empty($settingParam)) {
            throw new NotFoundResourceException("Order not found.");
        }

        if (
            config('app.is_check_sign') !== false
            && (!isset($data[$this->getKeySign()]) || $data[$this->getKeySign()] != $this->createCallbackSign($data, $settingParam))
        ) {
            throw new NotFoundResourceException("Sign error.");
        }

        if (in_array($order->status, [
            Status::CALLBACK_SUCCESS,
            Status::CALLBACK_FAILED,
            Status::TERMINATED,
        ])) {
            throw new StatusLockedException($this->getSuccessReturn());
        }

        if ($status === false) {
            return new CallbackResult(false, $this->getSuccessReturn(), $order);
        }

        return new CallbackResult(true, $this->getSuccessReturn(), $order, $data[$this->getKeyAmount()]);
    }

    protected abstract function createCallbackSign($data, SettingParam $key);

    protected function getKeyStatus()
    {
        return $this->keyStatus;
    }

    protected function getKeyStatusSuccess()
    {
        return $this->keyStatusSuccess;
    }

    protected function getKeyOrderId()
    {
        return $this->keyOrderId;
    }

    protected function getKeySign()
    {
        return $this->keySign;
    }

    protected function getKeyAmount()
    {
        return $this->keyAmount;
    }

    protected function getSuccessReturn()
    {
        return $this->successReturn;
    }
}
