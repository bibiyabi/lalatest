<?php

namespace App\Services\Payments\DepositGateways;

use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Models\Order;
use App\Models\Setting;
use App\Constants\Payments\PlaceholderParams as P;

class Inrusdt implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    private $method = 'post';

    private $url = 'https://www.inrusdt.com';

    private $returnType = 'form';

    private $keyStatus = 'status';

    private $keyOrderId = 'orderId';

    private $keySign = 'sign';

    private $keyAmount = 'amount';

    private $successReturn = 'success';

    protected function createParam(Order $order, Setting $key): array
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

    public function processOrderResult($unprocessed): string
    {
        return $unprocessed;
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

    public function getPlaceholder():array
    {
        return [
            P::PUBLIC_KEY  => 'hello world',
            P::PRIVATE_KEY => '666',
            P::MD5_KEY => '666',
            P::NOTIFY_URL  => 'http://google.com',
            P::RETURN_URL  => 'http://google.com',
            P::TRANSACTION_TYPE  => [
                0 => 'UPI',
                1 => 'PAYATM',
            ],
            P::COIN  => [
                0 => 'USDT',
                1 => 'BITCOIN'
            ],
            P::BLOCKCHAIN_CONTRACT => [
                0 => 'TR20',
                1 => 'CC60'
            ],
            P::API_KEY => 'key',
            P::NOTE1 => 'lala',
            P::NOTE2 => 'yoyo',
        ];
    }
}
