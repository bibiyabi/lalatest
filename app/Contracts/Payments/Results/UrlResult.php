<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\OrderParam;
use Illuminate\Support\Facades\Http;

class UrlResult implements ResultFactory
{
    public function getResult(OrderParam $param)
    {
        $method = $param->getMethod();
        $result = Http::$method($param->getUrl(), $param->getBody())
            ->headers($param->getHeader());

        return $result;
    }
}
