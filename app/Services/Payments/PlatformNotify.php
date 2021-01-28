<?php
namespace App\Services\Payments;


use App\Payment\Curl;
use App\Repositories\MerchantRepository;
use App\Contracts\Payments\LogLine;
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

        $url = $this->javaUrl . '/withdraw/result';
        $postData = [];
        $postData['orderId'] = $this->order->order_id;
        $postData['amount'] = (string) $this->order->real_amount;
        $postData['status'] = self::SUCCESS;
        $postData['signature'] =  Signature::makeSign($postData, $this->javaKey);

        $this->curlRes = $this->curl->setUrl($url)
            ->setPost($postData)
            ->exec();

        Log::channel('withdraw')->info(new LogLine('通知JAVA'), ['url' => $url, 'post' => $postData, 'res' => $this->curlRes]);
    }

    public function notifyWithdrawFailed() {

        $url = $this->javaUrl . '/withdraw/result';

        $postData = [];
        $postData['orderId'] = $this->order->order_id;
        $postData['amount'] = "";
        $postData['status'] = self::FAIL;
        $postData['signature'] = Signature::makeSign($postData, $this->javaKey);

        $this->curlRes = $this->curl->setUrl($url)
            ->setPost($postData)
            ->exec();

        Log::channel('withdraw')->info(new LogLine('通知JAVA'), ['url' => $url, 'post' => $postData, 'res' => $this->curlRes]);

    }

    public function checkSuccess($res) {
        $javaRes = json_decode($res, true);
        if (!isset($javaRes['status']) || $javaRes['status'] !== '200') {
            throw new WithdrawException('java res failed');
        }
    }

}
