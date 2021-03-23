<?php
namespace App\Exceptions;

use Exception;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Illuminate\Http\Request;

class WithdrawException extends Exception
{
    public function render(Request $request)
    {
        return RB::asError($this->getCode())->withMessage($this->getFile(). $this->getCode() . $this->getMessage())->build();
    }
}
