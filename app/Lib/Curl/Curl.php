<?php
namespace  App\Lib\Curl;
use Illuminate\Support\Facades\Log;
class Curl
{
    const STATUS_SUCCESS = 1;
    const TIMEOUT = 2;
    const FAILED = 4;

    private $ch = null;
    private $second;
    private $errorMsg = '';
    private $header;

    public function __construct() {
        $this->ch = curl_init();
        $this->basic();
    }

    private function setOpt($option, $param) {
        if ($this->ch == null) {
            $this->ch = curl_init();
            $this->basic();
        }
        curl_setopt($this->ch, $option, $param);
    }

    public function setUrl($url) {
        $this->setOpt(CURLOPT_URL, $url);
        return $this;
    }

    public function setTimeoutSecond($second = 5) {
        $this->second = $second;
        return $this;
    }

    public function setHeader($array) {
        $this->header = $array;
        $this->setOpt(CURLOPT_HTTPHEADER, $array);
        return $this;
    }

    public function followLocation() {
        $this->setOpt(CURLOPT_FOLLOWLOCATION, true);
        return $this;
    }

    public function setPost($post) {
        $this->setOpt(CURLOPT_POST, 1);
        $this->setOpt(CURLOPT_POSTFIELDS, $post);
        return $this;
    }

    public function basic() {
        $this->setTimeoutSecond(10);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLOPT_TIMEOUT, $this->second);
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $this->second);

        return $this;
    }

    public function setErrorMsg($msg) {
        $this->errorMsg = $msg;
        return $this;
    }

    public function ssl($bollean = false){
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, $bollean ? 2 : $bollean);
        $this->setOpt(CURLOPT_SSL_VERIFYPEER, $bollean);
        return $this;
    }

    public function exec() {

        //$info = curl_getinfo($this->ch);
        $curlResult = curl_exec($this->ch);
        $errorNo = curl_errno($this->ch);
        $errorMsg = curl_error($this->ch);
        curl_close($this->ch);
        $this->ch  = null;

        if ($errorNo) {
            if ($errorNo === 28) {
                return ['code' => self::TIMEOUT ,'data' => [], 'errorMsg' => $errorMsg];
            }
            return ['code' => self::FAILED ,'data' => [], 'errorMsg' => $errorMsg];
        }

        $res = ['code' => self::STATUS_SUCCESS, 'data' => $curlResult, 'errorMsg' => ''];
        return $res;
    }

}
