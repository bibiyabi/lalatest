<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\PlaceholderParams as P;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Payment\Curl;
use App\Payment\Proxy;
use App\Services\Payments\ResultTrait;

class ApplePay extends AbstractWithdrawGateway
{
    use ResultTrait;
    use Proxy;

    private $curlPostData;
    private $curlRes;
    private $curl;

    public function __construct(Curl $curl) {
        $this->curl = $curl;
    }

    public function setRequest($data) {

       Log::channel('withdraw')->info(__LINE__ , $data);

        $validator = Validator::make($data, [
            'order_id' => 'required',
            'address' => 'required',
            'rate_amount' => 'required'
        ]);

        if($validator->fails()){
            return "您輸入的資料有誤";
        }
        # set data
       $this->curlPostData['order_id'] = $data['order_id'];
       $this->curlPostData['sign'] = $this->genSign();
       return $this;
    }

    private function genSign() {
        return $this;
    }


    public function send() {
        echo '@@send';
        $url = $this->getServerUrl(). '/DMAW2KD7/autoPay/sendOrder.zv';
        $this->curlRes = $this->curl->setUrl( $url)
            ->setHeader([])
            ->setPost($this->curlPostData)
            ->exec();

        if (true) {
            return $this->resCreateSuccess();
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

    public function getPlaceholder():array
    {
        return [
            P::PUBLIC_KEY  => 'hello world',
            P::PRIVATE_KEY => '666',
            P::MD5_KEY => '666',
            P::NOTIFY_URL  => 'http://google.com',
            P::RETURN_URL  => 'http://google.com',
            P::TRANSACTION_TYPE  => [
                0 => 'UPI',
                1 => 'PAYATM',
            ],
            P::COIN  => [
                0 => 'USDT',
                1 => 'BITCOIN'
            ],
            P::BLOCKCHAIN_CONTRACT => [
                0 => 'TR20',
                1 => 'CC60'
            ],
            P::API_KEY => 'key',
            P::NOTE1 => 'lala',
            P::NOTE2 => 'yoyo',
        ];
    }



}
