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
use App\Exceptions\TpartyException;

class InPay implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    # 下單方式 get post
    private $method = 'form';

    # 第三方域名
    private $url = 'http://104.149.202.6:8084';

    # 充值 uri
    private $orderUri = '/api/startOrder';

    # 下單方式 form url
    private $returnType = 'url';

	# 下單欄位名稱-簽章 null or string
	private $orderKeySign = 'sign';

    # 回調欄位名稱-狀態
    private $keyStatus = 'state';

    # 回調欄位成功狀態值
    private $keyStatusSuccess = 1;

    # 回調欄位名稱-訂單編號
    private $keyOrderId = 'orderNo';

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
            'merchantNum' => $settings->getMerchant(),
            'orderNo' => $param->getOrderId(),
            'amount' => sprintf("%.2f", $param->getAmount()),
            'notifyUrl' => config('app.url') . '/callback/deposit/InPay',
			'returnUrl' => 'aaa',
            'payType' => $settings->getTransactionType(),
        ];
    }

    protected function getHeader(SettingParam $settingParam, OrderParam $orderParam, $param): array
    {
        return ['Content-Type: application/x-www-form-urlencoded'];
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
        $keys = ['merchantNum', 'orderNo', 'amount', 'notifyUrl'];
        $md5str = '';
        foreach ($keys as $k) {
			$md5str .= $param[$k];
        }
        $md5str .= $key->getMd5Key();

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

        if (isset($data['data']['payUrl']) === false) {
            throw new TpartyException($data['msg'] ?? "tparty error.");
        }

        return $data['data']['payUrl'];
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
		    'state'       => $param['state'],
            'merchantNum' => $param['merchantNum'],
            'orderNo'     => $param['orderNo'],
            'amount'      => $param['amount'],
            'key'         => $key->getMd5Key(),
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
                $transactionType = [];
                break;

            case Type::WALLET:
                $transactionType = ['upi'];
                break;

			case Type::BANK_CARD:
                $transactionType = ['bankCard'];
                break;

            default:
                $transactionType = [];
                break;
        }

        return new Placeholder(
            $type,
            '',
            'Please input 商户号',
            '',
            '',
            'Please input KEY',
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


        return new DepositRequireInfo($type, $column, []);
    }
}
