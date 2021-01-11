<?php
namespace  App\Payment;

class Curl
{
    const STATUS_SUCCESS = 1;
    const TIMEOUT = 2;
    const FAILED = 4;

    private $ch;
    private $second;
    private $errorMsg;

    public function __construct() {
        $this->ch = curl_init();
        $this->basic();
    }

    public function setUrl($url) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return $this;
    }

    public function setTimeoutSecond($second = 5) {
        $this->second = $second;
        return $this;
    }

    public function setHeader($array) {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $array);
        return $this;
    }

    public function setPost($post) {
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
        return $this;
    }

    public function basic() {
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->second);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->second);
        return $this;
    }

    public function setErrorMsg($msg) {
        $this->errorMsg = $msg;
        return $this;
    }

    public function exec() {

        curl_exec($this->ch);

        $errorNo = curl_errno($this->ch);

        if ($errorNo) {
            if ($errorNo === 28) {
                return ['msg' => 'timeout', 'code' => self::TIMEOUT];
            }
            $this->errorMsg = curl_error($this->ch);
            return ['msg' => $this->errorMsg, 'code' => self::FAILED];
        }
        curl_close($this->ch);
    }

}
