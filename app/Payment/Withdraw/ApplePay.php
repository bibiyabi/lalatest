<?php
namespace App\Payment\Withdraw;

use App\Services\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\Curl;
use App\Payment\Withdraw\CreateRes;
class ApplePay extends AbstractWithdrawGateway
{
    use CreateRes;

    private $postData;
    private $curlRes;

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

        return $this->setData($data)->genSign()->send(new Curl);
    }

    private function setData($data) {
        return $this;
    }

    private function genSign() {
        return $this;
    }


    public function send(Curl $curl) {
        $url = $this->getServerUrl(). '/DMAW2KD7/autoPay/sendOrder.zv';
        $this->curlRes = $curl->setUrl($url)->setHeader([])->setPost($this->postData)->exec();
        return $this;
    }


    protected function returnOrderRes()
    {
        if ($this->curlRes['code'] == 0) {
            return $this->resSuccess();
        }
    }


}
