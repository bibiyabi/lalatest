<?php

namespace App\Services\Payments\DepositGateways;

use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\Deposit\DepositRequireInfo;
use App\Contracts\Payments\Placeholder;
use App\Constants\Payments\DepositInfo as C;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use App\Constants\Payments\Type;
use App\Exceptions\TpartyException;
use App\Exceptions\UnsupportedTypeException;

class Pay777 implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    # 下單方式 get post
    private $method = 'post';

    # 第三方域名
    private $url = 'https://api.zf77777.org';

    # 充值 uri
    private $orderUri = '/api/create';

    # 下單方式 form url
    private $returnType = 'url';

    # 下單欄位名稱-簽章 null or string
    private $orderKeySign = 'sign';

    # 回調欄位名稱-狀態
    private $keyStatus = 'success';

    # 回調欄位成功狀態值
    private $keyStatusSuccess = '1';

    # 回調欄位名稱-訂單編號
    private $keyOrderId = 'orderid';

    # 回調欄位名稱-簽章
    private $keySign = 'sign';

    # 回調欄位名稱-金額
    private $keyAmount = 'bmount';

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
            'userid'    => $settings->getMerchant(),
            'orderid'   => $param->getOrderId(),
            'type'      => $param->getTransactionType() ?: $settings->getTransactionType(),
            'amount'    => $param->getAmount($param::AMOUNT_INT),
            'notifyurl' => config('app.url') . '/callback/deposit/Pay777',
            'returnurl' => $settings->getReturnUrl(),
        ];
    }

    protected function getHeader(SettingParam $settingParam, OrderParam $orderParam, $param): array
    {
        return ['Content-Type: application/json'];
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
        $keys = ['orderid', 'amount'];
        $md5str = $key->getMd5Key();
        foreach ($keys as $k) {
            $md5str .= $param[$k];
        }

        return md5($md5str);
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

        if (isset($data['pageurl']) === false) {
            throw new TpartyException($data['message'] ?? "tparty error.");
        }

        return $data['pageurl'];
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
                $transactionType = ['upi', 'razorpay', 'instamojo'];
                break;

            case Type::BANK_CARD:
                $transactionType = ['sbi', 'icici'];
                break;

            default:
                $transactionType = [];
                break;
        }

        return new Placeholder(
            $type,
            '',
            'Please input userid',
            '',
            '',
            'Please input token',
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
            case Type::BANK_CARD:
                $column = [C::AMOUNT];
                break;

            case Type::WALLET:
                $column = [C::AMOUNT];
                break;

            default:
                throw new UnsupportedTypeException();
                break;
        }

        $bank = [
            [
                'id' => 'icici',
                'name'=>'IciciBank'
                ],
            [
                'id' => 'sbi',
                'name'=>'SbiBank'
            ]
        ];

        return new DepositRequireInfo($type, $column, $bank);
    }
}
