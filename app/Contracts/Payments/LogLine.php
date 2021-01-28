<?php
namespace App\Contracts\Payments;

use App\Exceptions\WithdrawException;
use Exception;

class LogLine extends \Exception
{
    private $msg;
    private $isInstantOfException = false;

    public function __construct($msg = '')
    {
        if ($msg instanceof WithdrawException) {
            $this->isInstantOfException = true;
            $this->msg = $this->createExceptionMsg($msg);
        } else {
            $this->msg = $msg;
        }
    }

    public function __toString()
    {
        if ($this->isInstantOfException) {
            return '';
        }
        return $this->msg . " \r\n file:" .$this->getFile().' line: '.$this->getLine()  . " \r\n";
    }

    private function createExceptionMsg($e) {
        return " message:" . $e->getMessage() .
        "\r\n code:" .  $e->getCode() .
        "\r\n file:" .  $e->getFile() .
        "\r\n line:" .  $e->getLine() . "\r\n";
    }

}
