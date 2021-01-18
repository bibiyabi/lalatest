<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Exceptions\WithdrawException;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Payment\Curl;
use App\Payment\Proxy;
use App\Services\Payments\ResultTrait;
use App\Constants\Payments\PlaceholderParams as P;
use App\Repositories\Orders\WithdrawRepository;
use Exception;
use App\Constants\Payments\ResponseCode;
use Illuminate\Http\Request;

class ShineUPay extends AbstractWithdrawGateway
{
    use ResultTrait;
    use Proxy;

    private $curlPostData = [];
    private $curlRes;
    private $curl;
    private $headerApiSign = '';
    private $setting;
    private $domain='testgateway.shineupay.com';
    private $withdrawRepository;

    public function __construct(Curl $curl, WithdrawRepository $withdrawRepository) {
        $this->curl = $curl;
        $this->withdrawRepository = $withdrawRepository;
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
       $setting['md5_key'] = 'fed8b982f9044290af5aba64d156e0d9';
       $setting['other_key1'] = 'https://testgateway.shineupay.com'; // 網域
       $setting['private_key'] = '673835da9a3458e88e8d483bdae9c9f1';  // 交易密碼MD5

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
       $this->curlPostData['body']['advPasswordMd5'] = $setting['private_key'];
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
       $this->curlPostData['body']['notifyUrl']     = 'http://zlcai88mb.1201s.com/api/notify/CGPay';

      // $this->curlPostData['body']['notifyUrl']      = config('app.url') . '/withdraw/callback/ShineUpay';

       $this->headerApiSign = $this->genSign(json_encode($this->curlPostData), $setting['md5_key']);



       return $this;
    }

    private function genSign($postData, $sign) {

        return md5($postData . '|'. $sign);
    }


    public function send() {
        $url = 'https://testgateway.shineupay.com/withdraw/create';

        /*
      $url = $this->getServerUrl(1) . '/withdraw/create';
       //

        echo '@@@@@@@@@@@@@@'.$url;

        $curlRes = $this->curl->ssl()->setUrl($url)->setHeader([
            'Content-Type: application/json',
            'Api-Sign: '. $this->headerApiSign,
            "HOST: testgateway.shineupay.com",
        ])->setPost([])->exec();
exit;*/


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

    public function getPlaceholder():array
    {
        return [
            P::PRIVATE_KEY => '提现密码',
            P::MD5_KEY => '商户秘钥',
        ];
    }

    public function getRequireColumns() {
        return [
            ['no'=> 1, 'data' => []]
        ];
    }








}
