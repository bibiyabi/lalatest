<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\HttpParam;
use App\Lib\Payments\Results\Result;

interface ResultFactoryInterface
{
    public function getResult(HttpParam $param): Result;
}
