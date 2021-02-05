<?php
namespace App\Contracts;

use App\Exceptions\WithdrawException;
use Exception;
use Throwable;

class LogLine extends \Exception
{
    private $msg;
    private $isInstantOfException = false;

    public function __construct($msg = '')
    {
        if ($msg instanceof Throwable) {
            $this->isInstantOfException = true;
            $this->msg = $this->createExceptionMsg($msg);
        } else {
            $this->msg = $msg;
        }
    }

    public function __toString()
    {
        if ($this->isInstantOfException) {
            return 'exception found => '  . $this->msg;
        }
        return $this->msg . " \r\n file=> " .$this->getFile()."\r\n line=> ".$this->getLine();
    }

    private function createExceptionMsg(Throwable $e) {
        return " message:" . $e->getMessage() .
        "\r\n code:" .  $e->getCode() .
        "\r\n file:" .  $e->getFile() .
        "\r\n line:" .  $e->getLine() . "\r\n";
    }

}
