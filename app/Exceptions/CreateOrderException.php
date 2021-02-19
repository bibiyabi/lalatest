<?php

namespace App\Exceptions;

use Exception;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class CreateOrderException extends Exception
{
    public function render()
    {
        return RB::asError($this->getCode())->withMessage($this->getFile(). $this->getCode() . $this->getMessage())->build();
    }
}
