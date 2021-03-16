<?php

namespace App\Services\Payments\DepositGateways;

use App\Services\Payments\Deposit\DepositGatewayTrait;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Services\Payments\Deposit\DepositRequireInfo;
use App\Contracts\Payments\Placeholder;
use App\Constants\Payments\DepositInfo as C;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use App\Constants\Payments\Type;
use App\Exceptions\TpartyException;
use App\Exceptions\UnsupportedTypeException;

class GlobalPay implements DepositGatewayInterface
{
    use DepositGatewayTrait;

    # 下單方式 get post
    private $method = 'post';

    # 第三方域名
    private $url = 'http://zvfdh.orfeyt.com';

    # 充值 uri
    private $orderUri = '/ty/orderPay';

    # 下單方式 form url
    private $returnType = 'url';

    # 下單欄位名稱-簽章 null or string
    private $orderKeySign = 'sign';

    # 回調欄位名稱-狀態
    private $keyStatus = 'status';

    # 回調欄位成功狀態值
    private $keyStatusSuccess = 'SUCCESS';

    # 回調欄位名稱-訂單編號
    private $keyOrderId = 'mer_order_no';

    # 回調欄位名稱-簽章
    private $keySign = 'sign';

    # 回調欄位名稱-金額
    private $keyAmount = 'pay_amount';

    # 回調成功回應值
    private $successReturn = 'SUCCESS';

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
            'mer_no'        => $settings->getMerchant(),
            'mer_order_no'  => $param->getOrderId(),
            'pname'         => $param->getFirstName() . ' ' . $param->getLastName(),
            'pemail'        => $param->getEmail(),
            'phone'         => $param->getMobile(),
            'order_amount'  => $param->getAmount($param::AMOUNT_FLOAT),
            'countryCode'   => 'IND',
            'ccy_no'        => 'INR',
            'busi_code'     => $settings->getTransactionType(),
            'goods'         => 'goods',
            'notifyUrl'     => config('app.url') . '/callback/deposit/GlobalPay',
            'pageUrl'       => $settings->getReturnUrl(),
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
        $md5str = urldecode(http_build_query($param)) . '&key=' . $key->getMd5Key();
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
            throw new TpartyException($data['err_msg'] ?? "tparty error.");
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
                $transactionType = ['UPI', 'Paytm'];
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
            'Please input pageUrl',
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
                $column = [
                    C::AMOUNT,
                    C::FIRST_NAME,
                    C::LAST_NAME,
                    C::MOBILE_NUMBER,
                    C::EMAIL,
                ];
                break;

            default:
                throw new UnsupportedTypeException();
                break;
        }

        $bank = [];

        return new DepositRequireInfo($type, $column, $bank);
    }
}
