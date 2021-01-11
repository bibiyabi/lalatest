<?php

namespace App\Services\Payments\DepositGateways;

use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Models\Order;
use App\Contracts\Payments\OrderResult;
use App\Contracts\Payments\Status;
use App\Models\Key;
use Illuminate\Http\Request;

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

    public function depositCallback(Request $request): OrderResult
    {
        $request = json_decode($request->body(), true);
        $status = $request['success'] == true ? (bool)$request['data']['status'] : false;

        if ($status === false) {
            return new OrderResult(false, $request['msg'] ?? '', Status::ORDER_FAILED);
        }

        return new OrderResult(true, 'success', Status::ORDER_SUCCES, $request['money']);
    }
}
