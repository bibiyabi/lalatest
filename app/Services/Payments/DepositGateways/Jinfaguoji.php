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

class Jinfaguoji implements DepositGatewayInterface
{
    use DepositGatewayHelper;

    # 下單方式 get post
    private $method = 'post';

    # 第三方域名
    private $url = 'http://pay.gzmnsy.com:8089';

    # 充值 uri
    private $orderUri = '/createOrder';

    # 下單方式 form url
    private $returnType = 'url';

    # 下單欄位名稱-簽章 null or string
    private $orderKeySign = null;

    # 回調欄位名稱-狀態
    private $keyStatus = 'status';

    # 回調欄位成功狀態值
    private $keyStatusSuccess = '00';

    # 回調欄位名稱-訂單編號
    private $keyOrderId = 'order_no';

    # 回調欄位名稱-簽章
    private $keySign = 'Api-Sign';

    # 回調欄位名稱-金額
    private $keyAmount = 'price';

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
		// int	金额，元为单位
		$num = floor($param->getAmount() * 10) % 10;
		$price = floor($param->getAmount());
		$num == 9 AND $price ++;

        return [
            'mer_no'   		=> $settings->getMerchant(),
            'order_no' 		=> $param->getOrderId(),
			'pname'    		=>  $param->getLastName().$param->getFirstName(),
			'pemail'   		=>  $param->getEmail(),
			'phone'    		=>  $param->getMobile(),
			'price' 		=>  $price,
			'country_code' 	=>  'IND',
            'pay_type' =>empty($settings->getTransactionType()) ? 'YDBANK': $settings->getTransactionType(),
            'notify_url'    => config('app.url') . '/callback/deposit/Jinfaguoji',
			'bankno'        => '000000',
			'bankcode'      => '000000',
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
        $string_a = http_build_query($param);
        $string_a = urldecode($string_a);
        $string_sign_temp = $string_a . "&key=" . $key->getMd5Key();

        $sign = md5($string_sign_temp);
        $result = strtoupper($sign);

        return $result;
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

        if (isset($data['data']['qrcode_url']) === false) {
            throw new TpartyException($data['message'] ?? "tparty error.");
        }

        return $data['data']['qrcode_url'];
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
                $transactionType = ['UPI'];
                break;

			case Type::BANK_CARD:
                $transactionType = [];
                break;

            default:
                $transactionType = [];
                break;
        }

        return new Placeholder(
            $type,
            '',
            'Please input MerchantID',
            '',
            '',
            'Please input MD5 Key',
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
                $column = [
						C::AMOUNT,
						C::LAST_NAME,
						C::FIRST_NAME,
						C::EMAIL,
						C::MOBILE_NUMBER,
					];
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
