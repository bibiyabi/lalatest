<?php
namespace App\Services\Payments;


use App\Payment\Curl;


class PlatformNotify
{
    private $curl;

    const SUCCESS = '000';
    const FAIL = '001';
    const JAVA_DOMAIN = '';

    const SIGN_KEY = 'b6687fdce21aabf3d2493c8350d4275f';

    public function __construct(Curl $curl) {
        $this->curl = $curl;
    }

    public function notifyWithdrawSuccess() {

        $url = self::JAVA_DOMAIN . 'withdraw/result';

        $postData = [];
        $postData['order_id'] = '';
        $postData['status'] = self::SUCCESS;
        $postData['signature'] = $this->makeSign($postData);

        $this->curlRes = $this->curl->setUrl($url)
            ->setPost([])
            ->exec();
    }

    public function notifyWithdrawFailed() {

        $url = self::JAVA_DOMAIN . 'withdraw/result';

        $postData = [];
        $postData['order_id'] = '';
        $postData['status'] = self::FAIL;
        $postData['signature'] = $this->makeSign($postData);

        $this->curlRes = $this->curl->setUrl($url)
            ->setPost([])
            ->exec();
    }

    private function removeEmptyData($data) {
        foreach ($data as $k=>$v) {
            if ($v == '') {
                unset($data[$k]);
            }
        }
        return $data;
    }

    private function makeSign($data)
    {
        $data = $this->removeEmptyData($data);
        ksort($data);
        $sign_str = urldecode(http_build_query($data));
        $signature = md5($sign_str . self::SIGN_KEY);

        return $signature;
    }




}
