<?php
namespace  App\Lib\Result;

class ResultMsg
{
    protected $msg;

    public function __construct($msg = '') {
        $this->msg = $msg;
    }

    public function getMsg() {
        return $this->msg;
    }

    public function setMessage($msg) {
        $this->msg = $msg;
    }


}
