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
        $column = [];
        switch ($type) {
            case Type::BANK_CARD:
                $column = [C::ACCT_FN, C::ACCT_LN, C::ACCT_NO, C::AMOUNT];
                break;

            case Type::WALLET:
                $column = [C::ACCT_NAME, C::ACCOUNT_ID, C::AMOUNT];
                break;

            case Type::CRYPTO_CURRENCY:
                $column = [C::CRYPTO_AMOUNT, C::ADDRESS, C::NETWORK];
                break;

            default:
                throw new UnsupportedTypeException();
                break;
        }

        return new DepositRequireInfo($type, $column);
    }
}
