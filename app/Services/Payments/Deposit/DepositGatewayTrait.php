<?php

namespace App\Services\Payments\Deposit;

use App\Contracts\Payments\CallbackResult;
use App\Models\Order;
use App\Contracts\Payments\HttpParam;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use App\Constants\Payments\Status;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use App\Exceptions\StatusLockedException;
use App\Exceptions\TpartyException;

trait DepositGatewayTrait
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
        if ($signKey = $this->getSignKey()) {
            $param[$signKey] = $this->createSign($param, $settingParam);
        }

        return new HttpParam(
            $this->getUrl($settingParam, $orderParam, $param),
            $this->getMethod($settingParam, $orderParam, $param),
            $this->getHeader($settingParam, $orderParam, $param),
            $param,
            $this->getConfig($settingParam, $orderParam, $param)
        );
    }

    abstract protected function createParam(OrderParam $orderParam, SettingParam $settingParam): array;

    abstract protected function createSign(array $param, SettingParam $key): string;

    protected function getUrl(SettingParam $settingParam, OrderParam $orderParam, $param): string
    {
        return $this->url . $this->orderUri;
    }

    protected function getHeader(SettingParam $settingParam, OrderParam $orderParam, $param): array
    {
        return [];
    }

    protected function getConfig(SettingParam $settingParam, OrderParam $orderParam, $param): array
    {
        return [];
    }

    protected function getSignKey()
    {
        return $this->orderKeySign;
    }

    protected function getMethod(SettingParam $settingParam, OrderParam $orderParam, $param)
    {
        return $this->method;
    }

    public function depositCallback(Request $request): CallbackResult
    {
        $orderId = $this->getOrderId($request);
        if ($orderId === null) {
            throw new TpartyException("Key " . $this->keyOrderId . " not found.");
        }

        $order = Order::where('order_id', $orderId)->firstOr(function () {
            throw new TpartyException("Order not found.");
        });

        if (in_array($order->status, [
            Status::CALLBACK_SUCCESS,
            Status::CALLBACK_FAILED,
            Status::TERMINATED,
        ])) {
            throw new StatusLockedException($this->getSuccessReturn());
        }

        $settingParam = SettingParam::createFromJson($order->key->settings);
        if (empty($settingParam)) {
            throw new TpartyException("Order not found.");
        }

        if (
            config('app.is_check_sign') !== false
            && $this->getSign($request) !== $this->createCallbackSign($request, $settingParam)
        ) {
            throw new TpartyException("Sign error.");
        }

        if ($this->getStatus($request) != $this->getStatusSuccess()) {
            return new CallbackResult(false, $this->getSuccessReturn(), $order);
        }

        return new CallbackResult(true, $this->getSuccessReturn(), $order, $this->getAmount($request));
    }

    protected function createCallbackSign($request, SettingParam $key): string
    {
        return $this->createSign($request->all(), $key); // 預設同下單簽名
    }

    protected function getOrderId(Request $request) {
        return $request->header($this->keyOrderId, $request->input($this->keyOrderId));
    }

    protected function getStatus(Request $request) {
        return $request->header($this->keyStatus, $request->input($this->keyStatus));
    }

    protected function getSign(Request $request): string {
        return $request->header($this->keySign, $request->input($this->keySign));
    }

    protected function getAmount(Request $request) {
        return $request->header($this->keyAmount, $request->input($this->keyAmount));
    }

    protected function getStatusSuccess()
    {
        return $this->keyStatusSuccess;
    }

    protected function getSuccessReturn()
    {
        return $this->successReturn;
    }
}
