<?php
namespace App\Services\Payments;


trait ProxyTrait
{
    public function getProxyIp($isHttp) {
        if ($isHttp) {
            return config('app.proxy_ip') . ':8443';
        }
        return  config('app.proxy_ip') . ':8080';
    }
}
