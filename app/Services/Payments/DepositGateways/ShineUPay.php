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
    private $keySign = null;

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
                'notifyUrl' => config('app.url') . '/callback/deposit/Inrusdt',
            ],
        ];
    }

    protected function getHeader($param, SettingParam $settingParam): array
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

        return $data['body']['content'];
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
        ];

        return new DepositRequireInfo($type, $column, []);
    }
}
