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
        return ;
    }

    public function render()
    {
        Log::info($this->getMessage());
        return RB::error(ResponseCode::GATEWAY_NOT_FOUND);
    }
}
