<?php

namespace App\Exceptions;

use App\Constants\Payments\ResponseCode;
use Exception;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class GatewayException extends Exception
{
    public function render()
    {
        return RB::error(ResponseCode::GATEWAY_ERROR);
    }
}
