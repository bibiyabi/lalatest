<?php

namespace App\Exceptions;

use App\Constants\Payments\ResponseCode;
use Exception;
use Illuminate\Support\Facades\Log;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class GatewayNotFountException extends Exception
{
    public function report()
    {
        return false;
    }

    public function render()
    {
        Log::info('Gateway Not Found Exception');
        return RB::error(ResponseCode::GATEWAY_NOT_FOUND);
    }
}
