<?php
namespace App\Services\Payments\Withdraw;
use App\Exceptions\WithdrawException;
use App\Facades\Curl;

use Illuminate\Support\Facades\Log;
use App\Lib\Hash\Signature;

class WithdrawNotify
{
    private $curl;
    private $order;
    private $javaKey;
    private $javaUrl;
    private $message = '';

    const SUCCESS = '000';
    const FAIL = '001';

    public function __construct() {
    }

    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    public function setOrder($order){
        $this->order = $order;
        $this->javaKey = config('app.sign_key');
        $this->javaUrl = config('app.java_domain');

        return $this;
    }

    public function notifyWithdrawSuccess() {
        $this->notify(self::SUCCESS);
    }

    public function notifyWithdrawFailed() {
        $this->notify(self::FAIL);
    }

    public function notify ($status) {

        $url = $this->javaUrl . '/withdraw/result';

        $postData = [];
        $postData['orderId'] = $this->order->order_id;
        $postData['amount'] = empty($this->order->real_amount) ? "0" : (string)$this->order->real_amount;
        $postData['status'] = $status;
        $postData['message'] = $this->message;

        $signArray = $postData;
        unset($signArray['message']);
        $postData['signature'] = Signature::makeSign($signArray, $this->javaKey);
        $this->curlRes = Curl::setUrl($url)
            ->setPost($postData)
            ->exec();

        Log::channel('withdraw')->info(new \App\Lib\Log\LogLine('通知JAVA'), ['url' => $url, 'post' => $postData, 'res' => $this->curlRes]);

        return $this->checkSuccess($this->curlRes['data']);
    }

    public function checkSuccess($res) {
        $javaRes = json_decode($res, true);
        if (!isset($javaRes['status']) || $javaRes['status'] !== '200') {
            throw new WithdrawException('java res failed');
        }
        return true;
    }

}
