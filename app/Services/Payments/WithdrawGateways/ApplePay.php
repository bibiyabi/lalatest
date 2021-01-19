<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\PlaceholderParams as P;
use App\Contracts\Payments\Placeholder;
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

    public function setRequest($data=[],$setting=[]) {

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

    public function getPlaceholder($type):Placeholder
    {
        $transactionType = [];


        return new Placeholder($type, '', '','請填上md5密鑰','http://商戶後台/recharge/notify',
            '',$transactionType);
    }



}
