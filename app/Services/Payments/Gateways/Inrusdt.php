<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Models\Order;
use App\Contracts\Payments\OrderResult;
use App\Contracts\Payments\Status;
use App\Models\Key;

class Inrusdt implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    public function getDepositHttpMethod(): string
    {
        return 'post';
    }

    protected function getUrl(): string
    {
        return ' https://www.inrusdt.com';
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
        return 'url';
    }

    public function processOrderResult($unprocessed): OrderResult
    {
        $unprocessed = json_decode($unprocessed, true);
        $status = $unprocessed['success'] == true ? (bool)$unprocessed['data']['status'] : false;

        if ($status === false) {
            return new OrderResult(false, $unprocessed['msg'], Status::ORDER_FAILED);
        }

        $msg = '';

        $orderResult = new OrderResult();
    }

    public function depositCallback(Order $order): OrderResult
    {

    }
}
