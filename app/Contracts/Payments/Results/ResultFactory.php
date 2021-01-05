<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\OrderParam;

interface ResultFactory
{
    public function getResult(OrderParam $param);
}