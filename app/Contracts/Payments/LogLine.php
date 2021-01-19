<?php
namespace App\Contracts\Payments;

class LogLine extends \Exception
{
    private $msg;
    public function __construct($msg)
    {
        $this->msg = $msg;
    }

    public function __toString()
    {
        return "\r\n" . $this->msg .' on file '.$this->getFile().':'.$this->getLine();
    }

}
