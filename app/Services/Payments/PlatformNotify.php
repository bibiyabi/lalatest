<?php
namespace App\Services\Payments;


use App\Payment\Curl;
use App\Repositories\MerchantRepository;
use App\Contracts\Payments\LogLine;
use Illuminate\Support\Facades\Log;

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
        $postData['order_id'] = $this->order->order_id;
        $postData['status'] = self::SUCCESS;
        $postData['signature'] = $this->makeSign($postData, $this->javaKey);

        $this->curlRes = $this->curl->setUrl($url)
            ->setPost($postData)
            ->exec();

        Log::channel('withdraw')->info(new LogLine('通知JAVA'), ['url' => $url, 'post' => $postData, 'res' => $this->curlRes]);
    }

    public function notifyWithdrawFailed() {

        $url = $this->javaUrl . '/withdraw/result';

        $postData = [];
        $postData['order_id'] = $this->order->order_id;
        $postData['status'] = self::FAIL;
        $postData['signature'] = $this->makeSign($postData, $this->javaKey);

        $this->curlRes = $this->curl->setUrl($url)
            ->setPost($postData)
            ->exec();

        Log::channel('withdraw')->info(new LogLine('通知JAVA'), ['url' => $url, 'post' => $postData, 'res' => $this->curlRes]);
    }

    private function removeEmptyData($data) {
        foreach ($data as $k=>$v) {
            if ($v == '') {
                unset($data[$k]);
            }
        }
        return $data;
    }

    private function makeSign($data, $ukey)
    {
        $data = $this->removeEmptyData($data);
        ksort($data);
        $sign_str = urldecode(http_build_query($data));
        $signature = md5($sign_str . $ukey);

        return $signature;
    }




}
