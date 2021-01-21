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

    protected $createSign;
    // {"id":1,"user_id":1,"md5_key":1,"gateway_id":3,"merchantId":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "673835da9a3458e88e8d483bdae9c9f1"}
    private $curlPostData = [];
    private $domain = 'testgateway.shineupay.com';
    private $callbackSuccessReturnString = 'success';
    private $createPostData;

    public function __construct(Curl $curl) {
        parent::__construct($curl);
    }

    public function setRequest($post = [], WithdrawOrder $order) {
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

    private function genSign($postData, $sign) {
        return md5($postData . '|'. $sign);
    }


    protected function setCreatePostData($post, $settings) {
        $this->createPostData = $this->getNeedGenSignArray($post,  $settings);
    }

    // 設定回調網址
    protected function setCallBackUrl() {
        $this->callbackUrl = config('app.url') . '/callback/withdraw/'. class_basename(__CLASS__);;
    }

    public function send() {
        $url = 'https://'.$this->domain. '/withdraw/create';

        $curlRes = $this->curl->ssl()
        ->setUrl($url)->setHeader([
            'Content-Type: application/json',
            'Api-Sign: '. $this->createSign,
            //"HOST: ".'testgateway.shineupay.com',
        ])->setPost(json_encode($this->createPostData))->exec();

        Log::channel('withdraw')->info(new LogLine('CURL 回應'), [$curlRes, $this->createPostData]);

        return $this->getSendReturn($curlRes);

    }

    protected function checkOrderIsSuccess($res) {
        return isset($res['body']['platformOrderId']) && $res['status'] == 0;
    }

    public function callback(Request $request) {

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



    protected function getCallbackValidateColumns() {
        return [
            'body.orderId' => 'required',
            'body.status' => 'required',
        ];
    }

    private function getCallbackResult($callbackPost) {

        switch ($callbackPost['body']['status']) {
            case 1:
                return $this->resCallbackSuccess($this->callbackSuccessReturnString, ['order_id' => $callbackPost['body']['orderId']]);

            case 2:
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
