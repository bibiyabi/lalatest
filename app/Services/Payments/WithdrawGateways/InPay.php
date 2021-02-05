<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\Withdraw\WithdrawRequireInfo;
use App\Exceptions\WithdrawException;
use App\Services\AbstractWithdrawGateway;
use App\Payment\Curl;
use Illuminate\Http\Request;
use App\Constants\Payments\WithdrawInfo as C;
use App\Contracts\LogLine;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Log;
use App\Constants\Payments\ResponseCode;
use App\Exceptions\InputException;

class InPay extends AbstractWithdrawGateway
{
    // ================ 下單參數 ==================
    // 下單domain
    protected $domain = '104.149.202.6:8084';
    // 下單網址
    protected $createSegments = '/api/startPayForAnotherOrder';
    // 設定下單sign
    protected $createSign;

    // ================ 回調參數 ==================
    // 停止callback回應的訊息
    protected $callbackSuccessReturnString = 'success';
    // 回調的orderId位置
    protected $callbackOrderIdPosition = 'body.orderId';
    // 回調的狀態位置
    protected $callbackOrderStatusPosition = 'body.status';
    protected $callbackOrderAmountPosition = 'body.amount';
    protected $callbackOrderMessagePosition = 'body.message';
    // 回調成功狀態
    protected $callbackSuccessStatus = [];
    // 回調確認失敗狀態
    protected $callbackFailedStatus = [2];

    public function __construct(Curl $curl) {
        parent::__construct($curl);
    }

    public function setRequest($post = [], WithdrawOrder $order) : void {
        $this->setBaseRequest($order, $post);
    }



    protected function validationCreateInput() {
        return [
            'order_id'         => 'required',
            'withdraw_address' => 'required',
            'amount'           => 'required',
            'upi_id'           => 'required',
        ];
    }


    protected function setCreateSign($post, $settings) {
        $signParams = $this->getNeedGenSignArray($post,  $settings);
        $this->createSign =  $this->genSign($signParams, $settings);
    }

    private function getNeedGenSignArray($input, $settings) {
        $this->setCallBackUrl(__CLASS__);
        if (empty($settings['merchant_number'])) {
            throw new InputException('merchant_username or private key not found', ResponseCode::ERROR_PARAMETERS);
        }
        $array = [];
        $array['merchantNum']          = $settings['merchant_number'];
        $array['orderNo']              = $input['order_id'];
        $array['amount']               = (float) $input['amount'];
        $array['notifyUrl']            = $this->callbackUrl;
        return $array;

    }

    protected function genSign($postData, $settings) {
        $str = '';
        foreach ($postData as $value) {
            $str .= $value;
        }
        $str .= $settings['md5_key'];
        return md5($str);
    }

    protected function setCreatePostData($post, $settings) {
        $array = [];
        $array['merchantNum']          = $settings['merchant_number'];
        $array['orderNo']              = $post['order_id'];
        $array['amount']               = (float) $post['amount'];
        $array['notifyUrl']            = $this->callbackUrl;
        $array['channelCode']          = 'upi';
        $array['upiId']                 = $post['upi_id'];
        $array['sign']                 = $this->createSign;
        $this->createPostData = $array;
    }

    protected function isHttps() {
        return false;
    }

    protected function getCurlHeader() {
        return [
        ];
    }

    protected function checkCreateOrderIsSuccess($res) {
        return isset($res['code']) && $res['code'] == 200;
    }

    // ===========================callback start===============================

    protected function getCallbackSign(Request $request) {
        return $request->header('api-sign');
    }

    public function  getCallBackInput(Request $request) {
        # 用php://input amount 小數點不會被削掉, request->post()會 ex:10.0000 => 10
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
        return new Placeholder($type,'','请填上商户编号', '', '提现密码','商户秘钥','',
        '',);
    }


    public function getRequireInfo($type): WithdrawRequireInfo
    {
        # 該支付有支援的渠道  指定前台欄位
        $column = [];
        if ($type == Type::BANK_CARD) {
            $column = [
                C::FUND_PASSWORD,
                C::BANK_ACCOUNT,
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