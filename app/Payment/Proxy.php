<?php
namespace App\Payment;

trait Proxy
{

    private $production_ip = '8.210.163.4';
    private $dev_ip = '47.52.40.40';

    /**
     * 走8080 我們往外就是 http://第三方
        走8443 我們往外就是 https://第三方
    */
    public function getServerUrl($https = 0) {

        if (env('APP_ENV', false) == 'ONLINE') {
            if ($https) {
                return 'http://' . $this->production_ip.':8443';
            }
            return 'http://' . $this->production_ip.':8080';
        }

        if ($https) {
            return  'http://' . $this->dev_ip.':8443';
        } else {
            return  'http://' . $this->dev_ip.':8080';
        }

    }

    public function getHeaderHost($domainWithHttp) {
        $host = str_replace('http://', '', $domainWithHttp);
        $host = str_replace('https://', '', $host);
        return $host;
    }



}
