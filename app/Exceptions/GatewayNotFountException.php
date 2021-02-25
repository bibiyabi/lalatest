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
        Log::info('Gateway Not Found Exception');
    }

    public function render()
    {
        return RB::error(ResponseCode::GATEWAY_NOT_FOUND);
    }
}
