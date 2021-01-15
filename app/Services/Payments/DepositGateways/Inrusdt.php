<?php

namespace App\Services\Payments\DepositGateways;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\Placeholder;
use App\Models\Order;
use App\Models\Setting;

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

    public function getPlaceholder($type):Placeholder
    {
        $transactionType = [];
        if ($type == config('params')['typeName'][3]){
            $transactionType = [
                0 => 'inrpay',
                1 => 'upi'
            ];
        }

        return new Placeholder($type, '', '','請填上md5密鑰','http://商戶後台/recharge/notify',
            '',$transactionType);
    }
}
