<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\HttpParam;
use Illuminate\Support\Facades\Http;

class UrlResult implements ResultFactoryInterface
{
    public function getResult(HttpParam $param)
    {
        $method = $param->getMethod();
        $result = Http::$method($param->getUrl(), $param->getBody())
            ->headers($param->getHeader());

        return $result;
    }
}
