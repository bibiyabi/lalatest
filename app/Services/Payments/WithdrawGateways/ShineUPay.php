<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\WithdrawRequireInfo;
use App\Exceptions\WithdrawException;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Payment\Curl;
use App\Payment\Proxy;
use App\Services\Payments\ResultTrait;
use App\Constants\Payments\ResponseCode;
use Illuminate\Http\Request;
use App\Constants\Payments\WithdrawInfo as C;
use App\Contracts\Payments\LogLine;
use App\Models\WithdrawOrder;

class ShineUPay extends AbstractWithdrawGateway
{
    use ResultTrait;
    use Proxy;
    # 建單sign
    protected $createSign;
    # 第三方domain
    private $domain = 'testgateway.shineupay.com';
    private $callbackSuccessReturnString = 'success';


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


    protected function createSign($post, $settings) {
        $signParams = $this->getNeedGenSignArray($post,  $settings);
        $this->createSign = $this->genSign(json_encode($signParams), $settings['md5_key']);
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

        dd($array);
        return $array;

    }

    private function genSign($postData, $sign) {
        return md5($postData . '|'. $sign);
    }


    protected function setCreatePostData($post, $settings) {
        $this->createPostData = $this->getNeedGenSignArray($post,  $settings);
    }



    public function send() {

        $url = 'https://'.$this->domain. '/withdraw/create';

        $curlRes = $this->curl->ssl()
        ->setUrl($url)
        ->setHeader([
            'Content-Type: application/json',
            'Api-Sign: '. $this->createSign,
            //"HOST: ".'testgateway.shineupay.com',
        ])
        ->setPost(json_encode($this->getCreatePostData()))
        ->exec();

        Log::channel('withdraw')->info(new LogLine('CURL 回應'), [$curlRes, $this->createPostData]);

        return $this->getSendReturn($curlRes);
    }



    public function callback(Request $request) {

        $post = $request->post();
        # 這個取價格小數點才不會有差 10.0000 依然是10.0000 request->post會消掉0
        $postJson  = file_get_contents("php://input");
        $postSign  = $request->header('api-sign');

        $this->validateCallbackInput($post);

        $order = WithdrawOrder::where('order_id', $post['body']['orderId'])->first();

        if (empty($order)) {
            throw new WithdrawException("Order not found." , ResponseCode::EXCEPTION);
        }

        $key = $order->key;

        if (empty($key)) {
            throw new WithdrawException("key not found." , ResponseCode::EXCEPTION);
        }

        $settings = $this->decode($key->settings);

        $checkSign = $this->genSign($postJson, $settings['md5_key']);

        if ($checkSign == $postSign) {
            return $this->getCallbackResult($post);
        }

    }

    protected function getCallbackValidateColumns() {
        return [
            'body.orderId' => 'required',
            'body.status' => 'required',
        ];
    }

    protected function checkOrderIsSuccess($res) {
        return isset($res['body']['platformOrderId']) && $res['status'] == 0;
    }

    private function getCallbackResult($callbackPost) {

        switch ($callbackPost['body']['status']) {
            case 1:
                return $this->resCallbackSuccess($this->callbackSuccessReturnString, ['order_id' => $callbackPost['body']['orderId']]);

            case 2:
                return $this->resCallbackFailed($this->callbackSuccessReturnString, ['order_id' => $callbackPost['body']['orderId']]);

            default:
                Log::channel('withdraw')->info(new LogLine('驗簽失敗'), ['callbackPost' => $callbackPost]);
                return $this->resCallbackFailed($this->callbackSuccessReturnString, ['order_id' => $callbackPost['body']['orderId']]);
        }

    }

    public function getPlaceholder($type):Placeholder
    {
        return new Placeholder($type, '', '','請填上md5密鑰','http://商戶後台/recharge/notify',
        '請填上同步通知地址',);
    }


    public function getRequireInfo($type): WithdrawRequireInfo
    {
        # 該支付有支援的渠道  指定前台欄位
        $column = [];
        if ($type == Type::BANK_CARD){
            $column = array(C::BANK,C::ACCOUNT,C::ADDRESS,C::AMOUNT, C::FIRST_NAME, C::LAST_NAME,
    C::MOBILE, C::EMAIL, C::IFSC);
        }elseif($type == Type::WALLET){
            $column = [C::ADDRESS,C::AMOUNT,C::ADDRESS];
        }

        return new WithdrawRequireInfo($type, $column, [], []);
    }
}
