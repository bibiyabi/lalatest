<?php

namespace App\Exceptions;

use App\Constants\Payments\ResponseCode;
use Exception;
use Illuminate\Support\Facades\Log;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class UnsupportedTypeException extends Exception
{
    public function report()
    {
        Log::info('Unsupported Type Exception');
    }

    public function render()
    {
        return RB::error(ResponseCode::UNSUPPORTED_TYPE);
    }
}
