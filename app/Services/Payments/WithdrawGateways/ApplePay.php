<?php
namespace App\Services\Payments\WithdrawGateways;

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
            'user_pk' => 'required',
            'sign' => 'required',
            'bankName' => '中国工商银行'
        ]);

        if($validator->fails()){
            return "您輸入的資料有誤";
        }
        # set data
       $this->curlPostData = $this->genSign();
       return $this;
    }

    private function genSign() {
        return $this;
    }


    public function send() {
        echo '@@send';
        $url = $this->getServerUrl(). '/DMAW2KD7/autoPay/sendOrder.zv';
        $this->curlRes = $this->curl->setUrl($this->curlPostData['url'])
            ->setHeader([])
            ->setPost($this->curlPostData['data'])
            ->exec();
        if (true) {
            return $this->resSuccess();
        }
    }




}