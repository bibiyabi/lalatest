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

    // {"id":1,"user_id":1,"md5_key":1,"gateway_id":3,"merchantId":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "673835da9a3458e88e8d483bdae9c9f1"}
    private $curlPostData = [];

    private $headerApiSign = '';
    private $domain = 'testgateway.shineupay.com';
    private $order;

    public function __construct(Curl $curl) {
        parent::__construct($curl);
    }

    public function setRequest($post = [], WithdrawOrder $order) {

        Log::channel('withdraw')->info(new LogLine('第三方參數'), ['post'=>$post, 'order' => $order]);

        if (empty($order)) {
            throw new WithdrawException('setting empty ', ResponseCode::ERROR_PARAMETERS);
        }
        $this->validateInput($post);
        $settings = $this->decode($order->key->settings);
        $this->setCallBackUrl();
        $this->createSendData($post,  $settings);
        $this->headerApiSign = $this->genSign(json_encode($this->curlPostData), $settings['md5_key']);

       return $this;
    }

    public function send() {
        $url = 'https://'.$this->domain. '/withdraw/create';

        $curlRes = $this->curl->ssl()
        ->setUrl($url)->setHeader([
            'Content-Type: application/json',
            'Api-Sign: '. $this->headerApiSign,
            //"HOST: ".'testgateway.shineupay.com',
        ])->setPost(json_encode($this->curlPostData))->exec();

        Log::channel('withdraw')->info(new LogLine('CURL 回應'), $curlRes);

        return $this->getSendReturn($curlRes);

    }

    public function callback(Request $request) {

       // dd($request->json()->all());
        $post = $request->post();
        //不用這個取價格小數點會有差
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
        Log::channel('withdraw')->info(new LogLine('驗簽失敗'), ['post' => $post, 'header' => $request->headers, 'order' => $order, 'key' => $key]);

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

        if ($type == Type::typeName[2]){
            $column = array(C::BANK,C::ACCOUNT,C::ADDRESS,C::AMOUNT, C::FIRST_NAME, C::LAST_NAME,
            C::MOBILE, C::EMAIL, C::IFSC);
        }elseif($type == Type::typeName[3]){
            $column = array(C::ADDRESS,C::AMOUNT,C::ADDRESS);
        }elseif($type == Type::typeName[4]){
            $column = array(C::CRYPTO_ADDRESS,C::CRYPTO_AMOUNT);
        }

        return new WithdrawRequireInfo($type, $column, [], ['CC','DD']);
    }

    private function getCallbackResult($callbackPost) {

        switch ($callbackPost['body']['status']) {
            case 1:
                return $this->resCallbackSuccess('success', ['order_id' => $callbackPost['body']['orderId']]);

            case 2:
                return $this->resCallbackFailed('success', ['order_id' => $callbackPost['body']['orderId']]);
        }

    }

    protected function getCallbackValidateColumns() {
        return [
            'body.orderId' => 'required',
            'body.status' => 'required',
        ];
    }

    private function createSendData($data, $settings) {

        $this->curlPostData['merchantId']             = $settings['merchantId'];
        $this->curlPostData['timestamp']              = time() . '000';
        $this->curlPostData['body']['advPasswordMd5'] = $settings['private_key'];
        $this->curlPostData['body']['orderId']        = $data['order_id'];
        $this->curlPostData['body']['flag']           = 0;
        $this->curlPostData['body']['bankCode']       = $data['withdraw_address'];
        $this->curlPostData['body']['bankUser']       = $data['first_name'] . $data['last_name'];
        $this->curlPostData['body']['bankUserPhone']  = $data['mobile'];
        $this->curlPostData['body']['bankAddress']    = $data['bank_address'];
        $this->curlPostData['body']['bankUserEmail']  = $data['email'];
        $this->curlPostData['body']['bankUserIFSC']   = $data['ifsc'];
        $this->curlPostData['body']['amount']         = (float) $data['amount'];
        $this->curlPostData['body']['realAmount']     = (float) $data['amount'];
        $this->curlPostData['body']['notifyUrl']      = $this->callbackUrl;

    }


    protected function getNeedValidateParams() {
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

    private function genSign($postData, $sign) {
        return md5($postData . '|'. $sign);
    }


    private function checkOrderIsSuccess($resData) {
        return isset($resData['body']['platformOrderId']) && $resData['status'] == 0;
    }


}
