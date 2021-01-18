<?php
namespace App\Exceptions;
use Exception;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Constants\Payments\ResponseCode;
use Illuminate\Http\Request;
class WithdrawException extends Exception
{
    public function render(Request $request)
    {
        return RB::asError(ResponseCode::ERROR_CONFIG_PARAMETERS)->withMessage($this->getMessage())->build();
    }
}
