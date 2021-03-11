<?php

namespace App\Services\Payments\DepositGateways;

use App\Services\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Services\Payments\Deposit\DepositRequireInfo;
use App\Contracts\Payments\Placeholder;
use App\Constants\Payments\DepositInfo as C;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use App\Exceptions\TpartyException;
use Str;

class ShineUPay implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    # 下單方式 get post
    private $method = 'post';

    # 第三方域名
    private $url = 'https://testgateway.shineupay.com';

    # 充值 uri
    private $orderUri = '/pay/create';

    # 下單方式 form url
    private $returnType = 'url';

    # 下單欄位名稱-簽章 null or string
    private $orderKeySign = null;

    # 回調欄位名稱-狀態
    private $keyStatus = 'status';

    # 回調欄位成功狀態值
    private $keyStatusSuccess = 1;

    # 回調欄位名稱-訂單編號
    private $keyOrderId = 'orderId';

    # 回調欄位名稱-簽章
    private $keySign = 'Api-Sign';

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
            'timestamp' => time() . '000',
            'body' => [
                'amount' => (float)$param->getAmount(),
                'orderId' => $param->getOrderId(),
                'details' => 'recharge',
                'userId' => Str::random(8),
                'notifyUrl' => config('app.url') . '/callback/deposit/ShineUPay',
            ],
        ];
    }

    protected function getHeader(SettingParam $settingParam, OrderParam $orderParam, $param): array
    {
        return [
            'Api-Sign' => $this->createSign($param, $settingParam),
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
        return md5(json_encode($param) . '|' . $key->getMd5Key());
    }

    /**
     * form 不用實作，url 回傳 url
     *
     * @param string $unprocessed form 會是 form、url 會是第三方回應
     * @return string
     */
    public function processOrderResult($unprocessed): string
    {
        $data = json_decode($unprocessed, true);

        if (isset($data['body']['content']) === false) {
            throw new TpartyException($data['msg'] ?? "tparty error.");
        }

        return $data['body']['content'];
    }

    /**
     * 後台設定提示字（英文）
     *
     * @param string $type
     * @return Placeholder
     */
    public function getPlaceholder($type): Placeholder
    {
        return new Placeholder(
            $type,
            '',
            'Please input MerchantID',
            '',
            '',
            'Please input MD5 Key',
            '',
            '',
        );
    }

    /**
     * 前台設定應輸入欄位
     *
     * @param string $type
     * @return \App\Services\Payments\Deposit\DepositRequireInfo
     */
    public function getRequireInfo($type): DepositRequireInfo
    {
        $column = [
            C::AMOUNT,
        ];

        return new DepositRequireInfo($type, $column, []);
    }
}
