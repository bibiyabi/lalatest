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
use App\Models\Order;
use App\Contracts\Payments\HttpParam;

class Dsupi implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    private $method = 'post';

    private $url = '';

    private $orderUri = '/index/unifiedorder?format=json';

    private $returnType = 'url';

    private $keyStatus = 'code';

    private $keyStatusSuccess = 1;

    private $keyOrderId = 'out_trade_no';

    private $keySign = 'sign';

    private $keyAmount = 'amount';

    private $successReturn = 'success';

    private $orderKeySign = 'sign';

    protected function createParam(OrderParam $param, SettingParam $settings): array
    {

        return [
            'appid' => $settings->getMerchant(),
            'out_trade_no' => $param->getOrderId(),
            'version' => 'v2.0',
            'pay_type' => $settings->getTransactionType() ?: $param->getTransactionType(),
            'amount' =>  sprintf("%.2f", $param->getAmount()),
            'callback_url' => config('app.url') . '/callback/deposit/Dsupi',
            'success_url' => '',
            'error_url' => '',
        ];
    }

    public function genDepositParam(Order $order): HttpParam
    {
        $settingParam = SettingParam::createFromJson($order->key->settings);
        $orderParam = OrderParam::createFromJson($order->order_param);

        $param = $this->createParam($orderParam, $settingParam);
        $param[$this->getSignKey()] = $this->createSign($param, $settingParam);

        return new HttpParam($this->getUrl($settingParam), $this->getMethod(), $this->getHeader($param, $settingParam), $param, $this->getConfig());
    }

    protected function getUrl(SettingParam $key): string
    {
        return  $key->getNote1() . $this->orderUri;
    }

    protected function createSign(array $data, SettingParam $key): string
    {
        $data = array_filter($data);
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);
        $string_sign_temp = $string_a . "&key=" . $key->getMd5Key();

        $sign = md5($string_sign_temp);
        $result = strtoupper($sign);

        return $result;
    }

    public function processOrderResult($unprocessed): string
    {
        return $unprocessed;
    }

    protected function createCallbackSign($param, $key): string
    {
      return $this->createSign($param, $key);
    }

    public function getPlaceholder($type): Placeholder
    {
        switch ($type) {

            case Type::WALLET:
                $transactionType = [];
                break;

            case Type::BANK_CARD:
                $transactionType = ['BankCardTransferBankCard'];
                break;

            default:
                $transactionType = [];
                break;
        }

        return new Placeholder(
            $type,
            'Please input 用户账号',
            'Please input 用户APPID',
            '',
            '',
            'Please input Key',
            '',
            '',
            $transactionType,
            null,
            null,
            '',
            '',
            'Please input 域名'
        );
    }

    # 該支付有支援的渠道  指定前台欄位
    public function getRequireInfo($type): DepositRequireInfo
    {
        switch ($type) {
            case Type::BANK_CARD:
                $column = [C::AMOUNT];
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
