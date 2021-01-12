<?php

namespace App\Services\Payments\DepositGateways;

use App\Contracts\Payments\CallbackResult;
use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Models\Order;
use App\Models\Key;
use Illuminate\Http\Request;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class Inrusdt implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    public function getDepositHttpMethod(): string
    {
        return 'post';
    }

    protected function getUrl(): string
    {
        return 'https://www.inrusdt.com';
    }

    protected function createParam(Order $order, Key $key): array
    {
        return [
            'merchantId' => $key->cashflowUserId,
            'userId' => $key->cashflowUserId,
            'payMethod' => $key->cashflowUserId,
            'money' => $key->cashflowUserId,
            'bizNum' => $key->cashflowUserId,
            'notifyAddress' => $key->cashflowUserId,
            'type' => $key->cashflowUserId,
        ];
    }

    protected function createSign($param, $key): string
    {
        ksort($param);
        $str = http_build_query($param);
        return md5($str . '&key=' . $key);
    }

    public function getReturnType(): string
    {
        return 'form';
    }

    public function processOrderResult($unprocessed): string
    {
        return $unprocessed;
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
            throw new NotFoundResourceException("Status not found");
        }

        if ($status === false) {
            return new CallbackResult(false, 'Order failed.', $order);
        }

        return new CallbackResult(true, $this->getCallbackSuccessReturn(), $order, $data[$this->getCallbackKeyAmount()]);
    }

    protected function createCallbackSign($param, $key): string
    {
        $data = [
            'merchantBizNum' => $param['merchantBizNum'],
            'merchantId'     => $param['merchantId'],
            'merchantPrice'  => $param['merchantPrice'],
            'money'          => $param['money'],
            'status'         => $param['status'],
            'sysBizNum'      => $param['sysBizNum'],
            'usdtAmount'     => $param['usdtAmount'],
            'key'            => $key->coin,
        ];

        return strtoupper(md5(http_build_query($data)));
    }

    protected function getCallbackKeyStatus()
    {
        return 'status';
    }

    protected function getCallbackKeyOrderId()
    {
        return 'merchantBizNum';
    }

    protected function getCallbackKeySign()
    {
        return 'sign';
    }

    protected function getCallbackKeyAmount()
    {
        return 'money';
    }

    protected function getCallbackSuccessReturn()
    {
        return 'ok';
    }
}
