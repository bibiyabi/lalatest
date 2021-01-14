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

class InPay extends AbstractWithdrawGateway
{
    use ResultTrait;
    use Proxy;

    private $curlPostData = [];
    private $curlRes;
    private $curl;

    public function __construct(Curl $curl) {
        $this->curl = $curl;
    }

    public function setRequest($data) {

       Log::channel('withdraw')->info(__LINE__ , $data);

       $data['order_id'] = '123456'.uniqid();
       $data['md5'] = 'qk19h31urqh3mkcrr72vewwhrrse1okk';
       $data['bank_user_name'] = '90660';
       $data['user_phone'] = '123456';
       $data['user_email'] = 'eee@gmail.com.tw';
       $data['bank_ifsc'] = '123456';
       $data['rate_amount'] = '90660';
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
           // throw new WithdrawException('input check error'. json_encode($validator->errors()) );
        }

        # set data
       $this->curlPostData['merchantNum'] = 'hotwin';
       $this->curlPostData['orderNo'] = '123456'.uniqid();
       $this->curlPostData['amount'] = 100;
       $this->curlPostData['notifyUrl'] = 'http://baidu.com/InPay';
       $this->curlPostData['channelCode'] = 'bankCard';

       $this->curlPostData['accountHolder'] = 'aaaa';
       $this->curlPostData['bankCardAccount'] = 'ccccc';
       $this->curlPostData['openAccountBank'] = 'fdddd';
       $this->curlPostData['ifsc'] = 'dddd';

       $this->curlPostData['sign'] = $this->genSign($this->curlPostData, '94573e1adef367065fef90edba65d588');

       return $this;
    }

    private function genSign($params, $md5) {

        $md5str = '';
        foreach ($params as $value) {
            $md5str .= $value;
        }
        $md5str .= $md5;

        return md5($md5str);
    }


    public function send() {
        echo '@@send';

        var_dump( $this->curlPostData);

        /*
            $url = $this->getServerUrl(0). '/api/startOrder';
            $this->curlRes = $this->curl->setUrl($url)
            ->setHeader([
                "HOST: 45.34.0.99:8084",
            ]);
        */

        $url = 'http://45.34.0.99:8084/api/startPayForAnotherOrder';
        $this->curlRes = $this->curl->setUrl($url);

        $curlRes = $this->curl->setPost($this->curlPostData)->exec();

        if ($curlRes['code'] == Curl::STATUS_SUCCESS) {
            return $this->resCreateSuccess();
        }
        if ($curlRes['code'] == Curl::FAILED) {
            return $this->resCreateSuccess();
        }
        if ($curlRes['code'] == Curl::TIMEOUT) {
            return $this->resCreateRetry();
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

    private function checkCallbackSign() {

    }




}
