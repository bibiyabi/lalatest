<?php
namespace App\Services\Payments;


use App\Payment\Curl;
use App\Repositories\MerchantRepository;
use App\Contracts\LogLine;
use App\Exceptions\WithdrawException;
use Illuminate\Support\Facades\Log;
use App\Services\Signature;

class PlatformNotify
{
    private $curl;
    private $order;
    private $javaKey;
    private $javaUrl;

    const SUCCESS = '000';
    const FAIL = '001';

    public function __construct(Curl $curl, MerchantRepository $repo) {
        $this->curl = $curl;
        $this->repo = $repo;

    }

    public function setOrder($order){
        $this->order = $order;
        $merchant = $order->merchant;

        $this->javaKey = $this->repo->getKey($merchant);
        $this->javaUrl = $this->repo->getNotifyUrl($merchant);

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
        $postData['signature'] = Signature::makeSign($postData, $this->javaKey);

        $this->curlRes = $this->curl->setUrl($url)
            ->setPost($postData)
            ->exec();

        Log::channel('withdraw')->info(new \App\Contracts\LogLine('通知JAVA'), ['url' => $url, 'post' => $postData, 'res' => $this->curlRes]);

        $this->checkSuccess($this->curlRes['data']);
    }

    public function checkSuccess($res) {
        $javaRes = json_decode($res, true);
        if (!isset($javaRes['status']) || $javaRes['status'] !== '200') {
            throw new WithdrawException('java res failed');
        }
    }

}
