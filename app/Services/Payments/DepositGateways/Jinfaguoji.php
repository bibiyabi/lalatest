<?php

namespace App\Services\Payments\DepositGateways;

use App\Contracts\Payments\Deposit\DepositGatewayHelper;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Contracts\Payments\Deposit\DepositRequireInfo;
use App\Contracts\Payments\Placeholder;
use App\Constants\Payments\DepositInfo as C;
use App\Constants\Payments\Status;
use App\Contracts\Payments\CallbackResult;
use App\Contracts\Payments\OrderParam;
use App\Contracts\Payments\SettingParam;
use App\Exceptions\StatusLockedException;
use App\Models\Order;
use Illuminate\Http\Request;
use Str;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

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
    private $keySign = null;

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

    public function depositCallback(Request $request): CallbackResult
    {
        $data = $request->all();
        $status = isset($data[$this->getKeyStatus()]) ? $data[$this->getKeyStatus()] == $this->getKeyStatusSuccess() : true;

        if (!isset($data[$this->getKeyOrderId()])) {
            throw new NotFoundResourceException("OrderId not found.");
        }

        $order = Order::where('order_id', $data[$this->getKeyOrderId()])->first();
        if (empty($order)) {
            throw new NotFoundResourceException("Order not found.");
        }

        $settingParam = SettingParam::createFromJson($order->key->settings);
        if (empty($settingParam)) {
            throw new NotFoundResourceException("Order not found.");
        }

        if (
            config('app.is_check_sign') !== false
            && $this->getKeySign() !== null
            && (!isset($data[$this->getKeySign()]) || $request->header('Api-Sign') != $this->createCallbackSign($data, $settingParam))
        ) {
            throw new NotFoundResourceException("Sign error.");
        }

        if (in_array($order->status, [
            Status::CALLBACK_SUCCESS,
            Status::CALLBACK_FAILED,
            Status::TERMINATED,
        ])) {
            throw new StatusLockedException($this->getSuccessReturn());
        }

        if ($status === false) {
            return new CallbackResult(false, $this->getSuccessReturn(), $order);
        }

        return new CallbackResult(true, $this->getSuccessReturn(), $order, $data[$this->getKeyAmount()]);
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

        return $data['data']['qrcode_url'];
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
        return $this->createSign($param, $key);
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
                $transactionType = ['upi'];
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
            'Please input merchantId',
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
     * @return DepositRequireInfo
     */
    public function getRequireInfo($type): DepositRequireInfo
    {
        $column = [
            C::AMOUNT,
			C::LAST_NAME,
			C::FIRST_NAME,
			C::EMAIL,
			C::MOBILE_NUMBER,
//			C::BANK_ACCOUNT,
//			C::BANK,
        ];
/* 泰国网银需上传真实银行账户		
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
*/
        return new DepositRequireInfo($type, $column, []);
    }
}
