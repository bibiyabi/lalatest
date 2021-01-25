<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\WithdrawRequireInfo;
use App\Exceptions\WithdrawException;
use App\Services\AbstractWithdrawGateway;
use App\Payment\Curl;
use Illuminate\Http\Request;
use App\Constants\Payments\WithdrawInfo as C;
use App\Contracts\Payments\LogLine;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Log;



class ShineUPay extends AbstractWithdrawGateway
{
    // ================ 下單參數 ==================
    // 下單domain
    protected $domain = 'testgateway.shineupay.com';
    // 下單網址
    protected $createSegments = '/withdraw/create';
    // 設定下單sign
    protected $createSign;

    // ================ 回調參數 ==================
    // 停止callback回應的訊息
    protected $callbackSuccessReturnString = 'success';
    // 回調的orderId位置
    protected $callbackOrderIdPosition = 'body.orderId';
    // 回調的狀態位置
    protected $callbackOrderStatusPosition = 'body.status';
    protected $callbackOrderAmountPosition = '';
    // 回調成功狀態
    protected $callbackSuccessStatus = [1];
    // 回調確認失敗狀態
    protected $callbackFailedStatus = [2];

    public function __construct(Curl $curl) {
        parent::__construct($curl);
    }

    public function setRequest($post = [], WithdrawOrder $order) : void {
        Log::channel('withdraw')->info(new LogLine('第三方參數'), ['post'=>$post, 'order' => $order]);
        $this->setBaseRequest($order, $post);
    }

    protected function validationCreateInput() {
        return [
            'order_id'         => 'required',
            'withdraw_address' => 'required',
            'first_name'       => 'required',
            'last_name'        => 'required',
            'mobile'           => 'required',
            'bank_address'     => 'required',
            'ifsc'             => 'required',
            'amount'           => 'required',
            'email'            => 'required',
        ];
    }


    protected function setCreateSign($post, $settings) {
        $signParams = $this->getNeedGenSignArray($post,  $settings);
        $this->createSign =  $this->genSign(json_encode($signParams), $settings);
    }

    private function getNeedGenSignArray($input, $settings) {
        $this->setCallBackUrl(__CLASS__);
        $array = [];
        $array['merchantId']             = $settings['merchantId'];
        $array['timestamp']              = time() . '000';
        $array['body']['advPasswordMd5'] = $settings['private_key'];
        $array['body']['orderId']        = $input['order_id'];
        $array['body']['flag']           = 0;
        $array['body']['bankCode']       = $input['withdraw_address'];
        $array['body']['bankUser']       = $input['first_name'] . $input['last_name'];
        $array['body']['bankUserPhone']  = $input['mobile'];
        $array['body']['bankAddress']    = $input['bank_address'];
        $array['body']['bankUserEmail']  = $input['email'];
        $array['body']['bankUserIFSC']   = $input['ifsc'];
        $array['body']['amount']         = (float) $input['amount'];
        $array['body']['realAmount']     = (float) $input['amount'];
        $array['body']['notifyUrl']      = $this->callbackUrl;

        return $array;

    }

    protected function genSign($postData, $settings) {
        return md5($postData . '|'. $settings['md5_key']);
    }

    protected function setCreatePostData($post, $settings) {
        $this->createPostData = json_encode($this->getNeedGenSignArray($post,  $settings));
    }

    protected function isCurlUseSSL() {
        return true;
    }

    protected function getCurlHeader() {
        return [
            'Content-Type: application/json',
            'Api-Sign: '. $this->getCreateSign(),
            //"HOST: ".'testgateway.shineupay.com',
        ];
    }

    protected function checkCreateOrderIsSuccess($res) {
        return isset($res['body']['platformOrderId']) && $res['status'] == 0;
    }

    // ===========================callback start===============================

    protected function getCallbackSign(Request $request) {
        return $request->header('api-sign');
    }

    protected function  getCallBackInput() {
        return  file_get_contents("php://input");
    }

    protected function getCallbackValidateColumns() {
        return [
            'body.orderId' => 'required',
            'body.status' => 'required',
        ];
    }

    protected function getCallbackOrderStatus($post) {
        return data_get($post, $this->callbackOrderStatusPosition);
    }

    // ======================= 下拉提示 ===========================

    public function getPlaceholder($type):Placeholder
    {
        return new Placeholder($type, '', '','請填上md5密鑰','http://商戶後台/recharge/notify',
        '請填上同步通知地址',);
    }


    public function getRequireInfo($type): WithdrawRequireInfo
    {
        # 該支付有支援的渠道  指定前台欄位
        $column = [];
        if ($type == Type::BANK_CARD) {
            $column = [
                C::FUND_PASSWORD,
                C::CRYPTO_ADDRESS,
                C::FIRST_NAME,
                C::LAST_NAME,
                C::MOBILE,
                C::BANK_ADDRESS,
                C::IFSC,
                C::AMOUNT,
                C::EMAIL,
            ];


        } elseif ($type == Type::WALLET) {
            $column = [C::ADDRESS, C::AMOUNT, C::ADDRESS];
        }

        return new WithdrawRequireInfo($type, $column, [], []);
    }
}
