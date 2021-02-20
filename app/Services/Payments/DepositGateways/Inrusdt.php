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

    # 下單方式 get post(x-www-form-urlencoded) form(form-data)
    private $method = 'get';

    # 第三方域名
    private $url = 'https://www.inrusdt.com';

    # 充值 uri
    private $orderUri = '/b/recharge';

    # 下單方式 form(回傳form 給前端跳轉) url(直接下單至第三方並返回儲值 url)
    private $returnType = 'form';

    # 下單欄位名稱-簽章 null or string
    private $orderKeySign = 'sign';

    # 回調欄位名稱-狀態
    private $keyStatus = 'status';

    # 回調欄位成功狀態值
    private $keyStatusSuccess = 1;

    # 回調欄位名稱-訂單編號
    private $keyOrderId = 'orderId';

    # 回調欄位名稱-簽章
    private $keySign = 'sign';

    # 回調欄位名稱-金額
    private $keyAmount = 'amount';

    # 回調成功回應值
    private $successReturn = 'success';

    /**
     * 建立下單參數
     *
     * @param OrderParam $param
     * @param SettingParam $settings
     * @return array
     */
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

    /**
     * 建立下單簽名
     *
     * @param array $param 下單參數
     * @param SettingParam $key 後臺設定參數
     * @return string
     */
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

    protected function getUrl(SettingParam $settingParam, OrderParam $orderParam, $param): string
    {
        return $this->url . $this->orderUri . '?' . http_build_query($param);
    }

    /**
     * 後台設定提示字（英文）
     *
     * @param string $type
     * @return Placeholder
     */
    public function getPlaceholder($type): Placeholder
    {
        switch ($type) {
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

    /**
     * 前台設定應輸入欄位
     *
     * @param string $type
     * @return DepositRequireInfo
     */
    public function getRequireInfo($type): DepositRequireInfo
    {
        switch ($type) {
            case Type::WALLET:
                $column = [C::AMOUNT];
                break;

            default:
                throw new UnsupportedTypeException();
                break;
        }


        return new DepositRequireInfo($type, $column, []);
    }
}
