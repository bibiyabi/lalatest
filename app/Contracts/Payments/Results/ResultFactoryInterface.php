<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\HttpParam;

interface ResultFactoryInterface
{
    public function getResult(HttpParam $param): Result;
}
