<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\HttpParam;
use Illuminate\Support\Facades\Http;

class UrlResult implements ResultFactoryInterface
{
    public function getResult(HttpParam $param)
    {
        $method = $param->getMethod();
        if ($method == 'post') {
            $result = Http::withOptions(['verify'=>false])->post($param->getUrl(), $param->getBody());
        } elseif ($method == 'get') {
            $result = Http::withOptions(['verify'=>false])->get($param->getUrl(), $param->getBody());
        }

        return $result;
    }
}
