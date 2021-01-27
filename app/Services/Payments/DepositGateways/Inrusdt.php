<?php

namespace App\Services\Payments\DepositGateways;

use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\DepositRequireInfo;
use App\Contracts\Payments\Placeholder;
use App\Constants\Payments\DepositInfo as C;
use App\Constants\Payments\Type;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use Str;

class Inrusdt implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    private $method = 'post';

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
            'money' => $param->getAmount(),
            'bizNum' => $param->getOrderId(),
            'notifyAddress' => config('app.url') . '/callback/deposit/Inrusdt',
            'type' => 'recharge',
        ];
    }

    protected function createSign($param, $key): string
    {
        ksort($param);
        $str = http_build_query($param);
        return md5($str . '&key=' . $key->md5);
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
        if ($type == Type::typeName[3]){
            $transactionType = [
                0 => 'inrpay',
                1 => 'upi'
            ];
        }

        return new Placeholder($type, '', '','請填上md5密鑰','http://商戶後台/recharge/notify',
            '',$transactionType);
    }

    # 該支付有支援的渠道  指定前台欄位
    public function getRequireInfo($type): DepositRequireInfo
    {
        $column = [];
        if ($type == Type::typeName[2]){
            $column = [C::ACCT_FN,C::ACCT_LN,C::ACCT_NO,C::AMOUNT];
        }elseif($type == Type::typeName[3]){
            $column = [C::ACCT_NAME,C::ACCOUNT_ID,C::AMOUNT];
        }elseif($type == Type::typeName[4]){
            $column = [C::CRYPTO_AMOUNT,C::ADDRESS,C::NETWORK];
        }

        return new DepositRequireInfo($type, $column);
    }
}
