<?php
namespace App\Services\Payments;

trait ProxyTrait
{
    public function getProxyIp($isHttp)
    {
        return config('app.proxy_ip') . ($isHttp ? ':8443' : ':8080');
    }
}
