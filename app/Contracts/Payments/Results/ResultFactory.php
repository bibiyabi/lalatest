<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\HttpParam;

interface ResultFactory
{
    public function getResult(HttpParam $param);
}