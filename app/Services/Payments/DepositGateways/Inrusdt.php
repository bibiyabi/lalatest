<?php

namespace App\Services\Payments\DepositGateways;

use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\Deposit\DepositRequireInfo;
use App\Contracts\Payments\Placeholder;
use App\Constants\Payments\DepositInfo as C;
use App\Constants\Payments\Type;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use App\Exceptions\UnsupportedTypeException;
use Str;

class Inrusdt implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    private $method = 'get';

    private $url = 'https://www.inrusdt.com';

    private $orderUri = '/b/recharge';

    private $returnType = 'form';

    private $keyStatus = 'status';

    private $keyStatusSuccess = 1;

    private $keyOrderId = 'orderId';

    private $keySign = 'sign';

    private $keyAmount = 'amount';

    private $successReturn = 'success';

    protected function createParam(OrderParam $param, SettingParam $settings): array
    {
        return [
            'merchantId' => $settings->getMerchant(),
            'userId' => Str::random(8),
            'payMethod' => $settings->getTransactionType(),
            'money' => $param->getAmount() * 100,
            'bizNum' => $param->getOrderId(),
            'notifyAddress' => config('app.url') . '/callback/deposit/Inrusdt',
            'type' => 'recharge',
        ];
    }

    protected function createSign(array $param, SettingParam $key): string
    {
        ksort($param);
        $md5str = '';
        foreach ($param as $k => $value) {
            if ($k == 'sign') continue;
            $md5str .= $k . '=' . $value . '&';
        }
        $md5str .= 'key=' . $key->getMd5Key();

        $sign = md5($md5str);
        return strtoupper($sign);
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

    public function getPlaceholder($type): Placeholder
    {
        switch ($type) {
            case Type::CREDIT_CARD:
                $transactionType = ['inrpay', 'upi'];
                break;

            case Type::WALLET:
                $transactionType = ['inrpay', 'upi'];
                break;

            default:
                $transactionType = [];
                break;
        }

        return new Placeholder(
            $type,
            '',
            'Please input 商户Id',
            '',
            '',
            'Please input APPKEY',
            '',
            '',
            $transactionType
        );
    }

    # 該支付有支援的渠道  指定前台欄位
    public function getRequireInfo($type): DepositRequireInfo
    {
        switch ($type) {
            case Type::BANK_CARD:
                $column = [C::AMOUNT];
                break;

            case Type::WALLET:
                $column = [C::AMOUNT, C::BANK];
                break;

            default:
                throw new UnsupportedTypeException();
                break;
        }
        #for test
        $bank = [
            0=>[
                'id' => '001',
                'name'=>'樂樂銀行'
                ],
            1=>[
                'id' => '003',
                'name'=>'悠悠銀行'
            ],
        ];

        return new DepositRequireInfo($type, $column, $bank);
    }
}
