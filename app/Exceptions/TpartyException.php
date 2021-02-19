<?php

namespace App\Exceptions;

use App\Constants\Payments\ResponseCode;
use Exception;
Use Illuminate\Support\Facades\Log;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class TpartyException extends Exception
{
    public function report()
    {

    }

    public function render()
    {
        Log::error('Tparty-Exception ' . $this->getMessage());
        return RB::asError(ResponseCode::TPARTY_ERROR)->withMessage($this->getMessage())->build();
    }
}
