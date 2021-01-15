<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Exceptions\WithdrawException;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Payment\Curl;
use App\Payment\Proxy;
use App\Services\Payments\ResultTrait;
use Exception;

class ShineUPay extends AbstractWithdrawGateway
{
    use ResultTrait;
    use Proxy;

    private $curlPostData = [];
    private $curlRes;
    private $curl;
    private $headerApiSign = '';
    private $setting;

    public function __construct(Curl $curl) {
        $this->curl = $curl;
    }

    public function setRequest($data = [], $setting = []) {
       $this->setting = $setting;
       Log::channel('withdraw')->info(__LINE__ , $data);

       $data['order_id']         = '123456'.uniqid();
       $data['email']            = 'aaa';
       $data['withdraw_address'] = '10ss';
       $data['first_name']       = 'aaa';
       $data['last_name']        = 'bbb';
       $data['mobile']           = '123456';
       $data['ifsc']             = '123456';
       $data['amount']           = 10;
       $data['bank_address']     = '123456';

       $setting['merchantId'] = 'A5LB093F045C2322';
       $setting['md5'] = 'fed8b982f9044290af5aba64d156e0d9';
       $setting['other_key1'] = 'https://testgateway.shineupay.com'; // 網域
       $setting['other_key2'] = '673835da9a3458e88e8d483bdae9c9f1';  // 交易密碼MD5
       $setting['async_address'] =  config('app.url') . '/withdraw/callback/ShineUpay';

        $validator = Validator::make($data, [
            'order_id'         => 'required',
            'withdraw_address' => 'required',
            'first_name'       => 'required',
            'last_name'        => 'required',
            'mobile'           => 'required',
            'bank_address'     => 'required',
            'ifsc'             => 'required',
            'amount'           => 'required',
            'email'            => 'required',
        ]);

        if ($validator->fails()) {
            throw new WithdrawException('input check error'. json_encode($validator->errors()));
        }

        # set data
       $this->curlPostData['merchantId']             = $setting['merchantId'];
       $this->curlPostData['timestamp']              = time() . '000';
       $this->curlPostData['body']['advPasswordMd5'] = $setting['other_key2'];
       $this->curlPostData['body']['orderId']        = $data['order_id'];
       $this->curlPostData['body']['flag']           = 0;
       $this->curlPostData['body']['bankCode']       = $data['withdraw_address'];
       $this->curlPostData['body']['bankUser']       = $data['first_name'] . $data['last_name'];
       $this->curlPostData['body']['bankUserPhone']  = $data['mobile'];
       $this->curlPostData['body']['bankAddress']    = $data['bank_address'];
       $this->curlPostData['body']['bankUserEmail']  = $data['email'];
       $this->curlPostData['body']['bankUserIFSC']   = $data['ifsc'];
       $this->curlPostData['body']['amount']         = $data['amount'];
       $this->curlPostData['body']['realAmount']     = $data['amount'];
       $this->curlPostData['body']['notifyUrl']      = $setting['async_address'];

       $this->headerApiSign = $this->genSign($this->curlPostData, $setting['md5']);

       return $this;
    }

    private function genSign($postData, $sign) {
        return md5(json_encode($postData) . '|'. $sign);
    }


    public function send() {

        $url = $this->getServerUrl(1) . '/withdraw/create';
        $curlRes = $this->curl->ssl()->setUrl($url)->setHeader([
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json',
            'Api-Sign:'. $this->headerApiSign,
            "HOST: ". $this->getHeaderHost($this->setting['other_key1']),

        ])->setPost(json_encode($this->curlPostData))->exec();


        if ($curlRes['code'] == Curl::STATUS_SUCCESS) {
            return $this->resCreateSuccess('', ['order_id' => $this->curlPostData['body']['orderId']]);
        }
        if ($curlRes['code'] == Curl::FAILED) {
            return $this->resCreateSuccess('', ['order_id' => $this->curlPostData['body']['orderId']]);
        }
        if ($curlRes['code'] == Curl::TIMEOUT) {
            return $this->resCreateRetry('', ['order_id' => $this->curlPostData['body']['orderId']]);
        }
    }

    public function callback($post) {

        Log::channel('withdraw')->info(__LINE__ , $post);

        $validator = Validator::make($post, [
            'order_id' => 'required',
        ]);

        if($validator->fails()){
            throw new WithdrawException('callback input check error'. json_encode($validator->errors()));
        }

        $checkSign = $this->checkCallbackSign();

        if ($checkSign) {
            return $this->resCallbackSuccess('', ['order_id' => $post['order_id']]);
        }

        return $this->resCallbackFailed('', ['order_id' => $post['order_id']]);

    }

    public function getPlaceholder():array {
        return [];
    }

    public function getRequireColumns() {
        return [
            ['no'=> 1, 'data' => []]
        ];
    }








}
