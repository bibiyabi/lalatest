<?php
namespace  App\Lib\Curl;
use Illuminate\Support\Facades\Log;
class Curl
{
    const STATUS_SUCCESS = 1;
    const TIMEOUT = 2;
    const FAILED = 4;

    private $ch;
    private $second;
    private $errorMsg = '';
    private $header;

    public function __construct() {
        $this->ch = curl_init();
        $this->basic();
    }

    public function setUrl($url) {
        if (!$this->ch) {
            $this->ch = curl_init();
            $this->basic();
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return $this;
    }

    public function setTimeoutSecond($second = 5) {
        $this->second = $second;
        return $this;
    }

    public function setHeader($array) {
        $this->header = $array;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $array);
        return $this;
    }

    public function followLocation() {

        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        return $this;
    }

    public function setPost($post) {
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
        return $this;
    }

    public function basic() {
        $this->setTimeoutSecond(10);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->second);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->second);

        return $this;
    }

    public function setErrorMsg($msg) {
        $this->errorMsg = $msg;
        return $this;
    }

    public function ssl($bollean = false){
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $bollean ? 2 : $bollean );
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $bollean);
        return $this;
    }

    public function exec() {

        //$info = curl_getinfo($this->ch);
        $curlResult = curl_exec($this->ch);
        $errorNo = curl_errno($this->ch);
        $errorMsg = curl_error($this->ch);
        curl_close($this->ch);

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