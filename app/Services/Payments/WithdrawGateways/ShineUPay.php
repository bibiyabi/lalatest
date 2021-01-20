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
use App\Repositories\Orders\WithdrawRepository;
use App\Constants\Payments\ResponseCode;
use Illuminate\Http\Request;
use App\Constants\Payments\WithdrawInfo as C;
use App\Models\WithdrawOrder;
use App\Models\Setting;

class ShineUPay extends AbstractWithdrawGateway
{
    use ResultTrait;
    use Proxy;

    // {"id":1,"user_id":1,"md5_key":1,"gateway_id":3,"merchantId":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "673835da9a3458e88e8d483bdae9c9f1"}
    private $curlPostData = [];
    private $curlRes;
    private $curl;
    private $headerApiSign = '';
    private $setting;
    private $domain='testgateway.shineupay.com';
    private $withdrawRepository;
    private $callbackUrl;

    public function __construct(Curl $curl, WithdrawRepository $withdrawRepository) {
        $this->curl = $curl;
        $this->withdrawRepository = $withdrawRepository;
        $this->callbackUrl = config('app.url') . '/withdraw/callback/'. __CLASS__;
    }

    public function setRequest($post = [], WithdrawOrder $order) {

        if (empty($order)) {
            throw new WithdrawException('setting empty ', ResponseCode::ERROR_PARAMETERS);
        }
        $this->validateInput($post);

        $settings = json_decode($order->key->settings, true);
        $this->createSendData($post,  $settings);

        $this->headerApiSign = $this->genSign(json_encode($this->curlPostData), $settings['md5_key']);

       return $this;
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

    private function validateInput($data) {
        $validator = Validator::make($data, $this->getNeedValidateParams());

        if ($validator->fails()) {
            throw new WithdrawException($validator->errors(), ResponseCode::ERROR_PARAMETERS);
        }
    }


    private function getNeedValidateParams() {
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


    public function send() {
        $url = 'https://'.$this->domain. '/withdraw/create';
        $curlRes = $this->curl->ssl()->setUrl($url)->setHeader([
            'Content-Type: application/json',
            'Api-Sign: '. $this->headerApiSign,
            "HOST: ".'testgateway.shineupay.com',
        ])->setPost(json_encode($this->curlPostData))->exec();

        # todo check order status
        if ($curlRes['code'] == Curl::STATUS_SUCCESS) {
            $resData = json_decode($curlRes['data'], true);
            if (isset($resData['body']['platformOrderId']) && $resData['status'] == 0) {
                return $this->resCreateSuccess('', ['order_id' => $this->curlPostData['body']['orderId']]);
            } else {
                return $this->resCreateFailed('', ['order_id' => $this->curlPostData['body']['orderId']]);
            }
        }
        if ($curlRes['code'] == Curl::FAILED) {
            return $this->resCreateFailed('', ['order_id' => $this->curlPostData['body']['orderId']]);
        }
        if ($curlRes['code'] == Curl::TIMEOUT) {
            return $this->resCreateRetry('', ['order_id' => $this->curlPostData['body']['orderId']]);
        }
    }

    public function callback(Request $request) {

        $postData  = $request->post();

        $postDecode = json_decode($postData, true);

        $postMd5  = $request->header('HTTP_API_SIGN');

        $validator = Validator::make($postDecode, [
            'body.orderId' => 'required',
        ]);

        if($validator->fails()){
            throw new WithdrawException('callback input check error'. json_encode($validator->errors()), ResponseCode::EXCEPTION);
        }

        $order = $this->withdrawRepository->filterOrderId($postDecode['body']['orderId'])->first();
        if (empty($order)) {
            throw new WithdrawException("Order not found." , ResponseCode::EXCEPTION);
        }

        $key = $order->key;
        if (empty($key)) {
            throw new WithdrawException("key not found." , ResponseCode::EXCEPTION);
        }

        $checkSign = $this->genSign($postData, $key->md5_key);

        if ($checkSign == $postMd5) {

            switch ($postDecode['body']['status']) {
                case 1:
                    return $this->resCallbackSuccess('success', ['order_id' => $postDecode['body']['orderId']]);

                case 2:
                    return $this->resCallbackFailed('success', ['order_id' => $postDecode['body']['orderId']]);
            }
        }

    }

    public function getPlaceholder($type):Placeholder
    {
        return new Placeholder($type, '', '','請填上md5密鑰','http://商戶後台/recharge/notify',
        '');
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


}
