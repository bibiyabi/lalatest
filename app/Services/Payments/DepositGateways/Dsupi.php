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
            'pay_type' =>empty($settings->getTransactionType()) ? 'CopyToBank': $settings->getTransactionType(),
            'amount' =>  sprintf("%.2f", $param->getAmount()),
            'callback_url' => config('app.url') . '/callback/deposit/Dsupi',
            'success_url' => '',
            'error_url' => '',
            'full_name' => $param->getTransactionType(),
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

    /**
     * form 直接回傳，url 回傳 url
     *
     * @param string $unprocessed form 會是 form、url 會是第三方回應
     * @return string
     */
    public function processOrderResult($unprocessed): string
    {
        $data = json_decode($unprocessed, true);

        return $data['data']['url'];
    }

    protected function createCallbackSign($param, $key): string
    {
      return $this->createSign($param, $key);
    }

    public function getPlaceholder($type): Placeholder
    {
        switch ($type) {
            case Type::BANK_CARD:
                $transactionType = [];
                break;

            case Type::WALLET:
                $transactionType = ['upi'];
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
                $column = [
                    C::AMOUNT,
                    C::BANK,
                ];
                break;

            case Type::WALLET:
                $column = [
                    C::AMOUNT,
                    C::BANK,
                ];
                break;


            default:
                throw new UnsupportedTypeException();
                break;
        }

        $bank = [
            [
                'id' => '1',
                'name'=>'ICIC'
                ],
            [
                'id' => '2',
                'name'=>'AXIS'
            ],
            [
                'id' => '3',
                'name'=>'HDFC'
            ],
            [
                'id' => '5',
                'name'=>'StateBankOfIndia'
            ],
            [
                'id' => '6',
                'name'=>'PunjabNationalBank'
            ],
            [
                'id' => '7',
                'name'=>'BankOfIndia'
            ],
            [
                'id' => '8',
                'name'=>'BankOfBaroda'
            ],
            [
                'id' => '9',
                'name'=>'CanaraBank'
            ],
            [
                'id' => '10',
                'name'=>'ReserveBankOfIndia'
            ],
            [
                'id' => '11',
                'name'=>'UnitedBankOfIndia'
            ],
            [
                'id' => '12',
                'name'=>'IndianOverseasBank'
            ],
            [
                'id' => '13',
                'name'=>'KotakMahindraBank'
            ],
        ];

        return new DepositRequireInfo($type, $column, $bank);
    }
}
