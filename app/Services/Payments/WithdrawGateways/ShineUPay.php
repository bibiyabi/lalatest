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

    public function __construct(Curl $curl) {
        $this->curl = $curl;
    }

    public function setRequest($data = []) {
       Log::channel('withdraw')->info(__LINE__ , $data);

       $data['order_id'] = '123456'.uniqid();
       $data['md5'] = 'fed8b982f9044290af5aba64d156e0d9';
       $data['other_key1'] = 'https://testgateway.shineupay.com';
       $data['bank_user_name'] = 'aaaa';
       $data['user_phone'] = '123456';
       $data['user_email'] = 'eee@gmail.com.tw';
       $data['bank_ifsc'] = '123456';
       $data['rate_amount'] = '10';
       $data['payment_address'] = '10ss';

        $validator = Validator::make($data, [
            'order_id'        => 'required',
            'bank_user_name'  => 'required',
            'user_phone'      => 'required',
            'payment_address' => 'required',
            'user_email'      => 'required',
            'bank_ifsc'       => 'required',
            'rate_amount'     => 'required',
            'other_key1'     => 'required',
        ]);


        if ($validator->fails()) {
            throw new WithdrawException('input check error'. json_encode($validator->errors()) );
        }
        echo __CLASS__;

        # set data
       $this->curlPostData['merchantId'] = 'A5LB093F045C2322';
       $this->curlPostData['timestamp'] = time() . '000';
       $this->curlPostData['body']['advPasswordMd5'] = '673835da9a3458e88e8d483bdae9c9f1';
       $this->curlPostData['body']['orderId'] = $data['order_id'];
       $this->curlPostData['body']['flag'] = 0;
       $this->curlPostData['body']['bankCode'] = $data['payment_address'];
       $this->curlPostData['body']['bankUser'] = $data['bank_user_name'];
       $this->curlPostData['body']['bankUserPhone'] = $data['bank_user_name'];
       $this->curlPostData['body']['bankAddress'] = 'aaa';
       $this->curlPostData['body']['bankUserEmail'] = $data['user_email'];
       $this->curlPostData['body']['bankUserIFSC'] = $data['bank_ifsc'];
       $this->curlPostData['body']['amount'] = 100;
       $this->curlPostData['body']['realAmount'] = 100;
       $this->curlPostData['body']['notifyUrl'] = env('APP_URL') . '/withdraw/callback/ShineUpay';


       $this->headerApiSign = $this->genSign($this->curlPostData, 'fed8b982f9044290af5aba64d156e0d9');


       return $this;
    }

    private function genSign($postData, $sign) {
        echo '@@sign:'. json_encode($postData) . '|'. $sign;
        return md5(json_encode($postData) . '|'. $sign);
    }


    public function send() {
        echo '@@send';

        var_dump( $this->curlPostData);

        /*
        $url = $this->getServerUrl(1). '/withdraw/create';
        $this->curlRes = $this->curl->setUrl($url)
            ->setHeader([
                "HOST: ". $this->getHeaderHost($this->curlPostData['other_key1']),
                'Content-Type: application/json; charset=UTF-8'
            ]);
*/
        $url = 'https://testgateway.shineupay.com/withdraw/create';
        $curlRes = $this->curl->ssl()->setUrl($url)->setHeader([
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json',
            'Api-Sign:'. $this->headerApiSign

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
            return "您輸入的資料有誤";
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
