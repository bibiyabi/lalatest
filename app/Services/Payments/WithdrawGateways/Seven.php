<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\Withdraw\WithdrawRequireInfo;
use App\Exceptions\UnsupportedTypeException;
use App\Services\AbstractWithdrawGateway;
use App\Payment\Curl;
use Illuminate\Http\Request;
use App\Constants\Payments\WithdrawInfo as C;
use App\Models\WithdrawOrder;
use App\Exceptions\InputException;
use App\Constants\Payments\Status;

class Seven extends AbstractWithdrawGateway
{
    // ================ 下單參數 ==================
    // 下單domain
    protected $domain = 'api.zf77777.org';
    // 下單網址
    protected $createSegments = '/api/withdrawal';
    protected $createResultMessagePosition = 'message';
    // 設定下單sign
    protected $createSign;
    protected $isCurlProxy = true;
    // ================ 回調參數 ==================
    // 停止callback回應的訊息
    protected $callbackSuccessReturnString = 'success';
    // 回調的orderId位置
    protected $callbackOrderIdPosition = 'orderid';
    // 回調的狀態位置
    protected $callbackOrderStatusPosition = 'iscancel';
    protected $callbackOrderAmountPosition = 'bmount';
    protected $callbackOrderMessagePosition = 'message';
    // 回調成功狀態
    protected $callbackSuccessStatus = [0];
    // 回調確認失敗狀態
    protected $callbackFailedStatus = [1];

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
            'first_name'       => 'required',
            'last_name'        => 'required',
            'bank_name'        => 'required',
            'ifsc'             => 'required',
            'amount'           => 'required',
        ];
    }


    protected function setCreateSign($post, $settings) {
        $this->createSign =  $this->genSign($post, $settings);
    }

    private function getNeedGenSignArray($input, $settings) {
        $this->setCallBackUrl(__CLASS__);
        if (empty($settings['merchant_number'])) {
            throw new InputException('merchant_username or private key not found', Status::ORDER_FAILED);
        }
        $array = [];
        $array['orderid']        = $input['order_id'];
        $array['userid']        = $settings['merchant_number'];
        $array['amount']         = $input['amount'];
        $array['type']         =  'bank';
        #$array['notifyUrl']      = 'http://admin02.6122028.com/callback/withdraw/Seven';
        $array['notifyUrl']      = $this->callbackUrl;
        $array['ordertype']      = 2;
        $array['returnurl']      = '';
        $payload = [];
        $payload['cardname'] = $input['first_name'] . $input['last_name'];
        $payload['cardno'] = $input['withdraw_address'];
        $payload['bankid'] = 10000;
        $payload['bankname'] = $input['bank_name'];
        $payload['ifsc'] = $input['ifsc'];
        $array['payload'] =  json_encode($payload);
        return $array;
    }

    protected function genSign($postData, $settings) {
        return strtolower(md5($settings['md5_key'] . $postData['order_id']. $postData['amount']));
    }

    protected function setCreatePostData($post, $settings) {
        $param = $this->getNeedGenSignArray($post, $settings);
        $param['sign'] = $this->createSign;
        $this->createPostData = json_encode($param);
    }

    protected function isHttps() {
        return true;
    }

    protected function getCurlHeader() {
        return [
            'Content-Type: application/json',
            "HOST: ". $this->domain
        ];
    }

    protected function checkCreateOrderIsSuccess($res) {
        return isset($res['success']) && $res['success'] == 1;
    }

    // ===========================callback start===============================

    protected function getCallbackSign(Request $request) {
        $post = $this->decode($this->getCallBackInput($request));
        return $post['sign'];
    }

    public function  getCallBackInput(Request $request) {
        # 用php://input amount 小數點不會被削掉, request->post()會 ex:10.0000 => 10
        return  file_get_contents("php://input");
    }

    protected function getCallbackValidateColumns() {
        return [
            'success' => 'required',
            'orderid' => 'required',
        ];
    }

    protected function genCallbackSign($postJson, $settings) {
        $post = $this->decode($postJson);
        $post['order_id'] = $post['orderid'];
        return $this->genSign($post, $settings);
    }

    // ======================= 下拉提示 ===========================

    public function getPlaceholder($type):Placeholder
    {
        return new Placeholder($type,'','Please input MerchantID', '', 'Please input Private Key', 'Please input MD5 Key','',
        '',);
    }


    public function getRequireInfo($type): WithdrawRequireInfo
    {
        # 該支付有支援的渠道  指定前台欄位
        switch ($type) {
            case Type::BANK_CARD:
                $column = [
                    C::AMOUNT,
                    C::IFSC,
                    C::BANK_ACCOUNT,
                    C::FIRST_NAME,
                    C::LAST_NAME,
                    C::FUND_PASSWORD,
                    C::BANK_NAME,
                ];
                break;


            default:
                throw new UnsupportedTypeException();
                break;
        }

        return new WithdrawRequireInfo($type, $column, [], []);
    }
}
