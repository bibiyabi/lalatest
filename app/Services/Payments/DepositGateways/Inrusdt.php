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

    # 下單方式 get post
    private $method = 'get';

    # 第三方域名
    private $url = 'https://www.inrusdt.com';

    # 充值 uri
    private $orderUri = '/b/recharge';

    # 下單方式 form url
    private $returnType = 'form';

    # 下單欄位名稱-簽章 null or string
    private $orderKeySign = null;

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

    /**
     * form 直接回傳，url 回傳 url
     *
     * @param string $unprocessed form 會是 form、url 會是第三方回應
     * @return string
     */
    public function processOrderResult($unprocessed): string
    {
        return $unprocessed;
    }

     /**
      * 建立回調簽名
      *
      * @param array $param request()->all
      * @param SettingParam $key
      * @return string
      */
    protected function createCallbackSign($param, SettingParam $key): string
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

    /**
     * 後台設定提示字（英文）
     *
     * @param string $type
     * @return Placeholder
     */
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
            #for test
            case Type::WALLET:
                $column = [
                    C::AMOUNT ,
                    C::BANK_NAME_INPUT ,
                    C::ACCT_NAME   ,
                    C::TXN_TIME  ,
                    C::UPLOAD_TXN   ,
                    C::CRYPTO_AMOUNT  ,
                    C::TXID       ,
                    C::DEPOSIT_AMOUNT  ,
                    C::BANK_NAME_FOR_CARD ,
                    C::CARD_NUMBER       ,
                    C::MOBILE_NUMBER     ,
                    C::ACCOUNT_ID      ,
                    C::EMAIL            ,
                    C::COUNTRY         ,
                    C::STATE           ,
                    C::CITY             ,
                    C::ADDRESS_WALLET   ,
                    C::ZIP             ,
                    C::LAST_NAME        ,
                    C::FIRST_NAME       ,
                    C::TELEGRAM        ,
                    C::EXPIRED_DATE     ,
                    C::BANK              ,
                    C::IFSC_INDIA        ,
                    C::BANK_PROVINCE     ,
                    C::BANK_ADDRESS      ,
                    C::BANK_CITY        ,
                    C::BANK_ACCOUNT      ,
                    C::UPI_ID            ,
                ];
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
