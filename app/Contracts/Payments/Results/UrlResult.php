<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\HttpParam;
use App\Exceptions\TpartyException;
use Illuminate\Support\Facades\Http;

class UrlResult implements ResultFactoryInterface
{
    public function getResult(HttpParam $param): Result
    {
        $method = $param->getMethod();

        try {
            switch ($method) {
                case 'form':
                $result = Http::asForm()
                    ->withHeaders($param->getHeader())
                    ->withOptions(['verify'=>false])
                    ->post($param->getUrl(), $param->getBody());
                    break;

                case 'post':
                $result = Http::withHeaders($param->getHeader())
                    ->withOptions(['verify'=>false])
                    ->post($param->getUrl(), $param->getBody());
                    break;

                case 'get':
                $result = Http::withHeaders($param->getHeader())
                    ->withOptions(['verify'=>false])
                    ->get($param->getUrl(), $param->getBody());
                    break;

                default:
                    throw new \Exception('');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new TpartyException("Couldn't resolve host.");
        }

        return new Result('url', $result);
    }
}
