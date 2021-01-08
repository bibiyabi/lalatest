<?php
namespace App\Services;
use Illuminate\Http\Request;
use App\Services\InputService;
use App\Collections\ApplePayCollection;
use App\Collections\BanksCollection;
use App\Collections\ApplePayBanksCollection;
abstract class AbstractWithdrawGateway
{

    const VIP_SERVER_IP = '8.210.163.4';
    const TEST_SERVER_IP = '47.52.40.40';

    public function __construct() {

    }


    /**
     * 走8080 我們往外就是 http://第三方
        走8443 我們往外就是 https://第三方
    */
    protected function getServerUrl($https = 0) {
        if (env('APP_ENV', false) == 'ONLINE') {
            if ($https) {
                return 'http://' . self::VIP_SERVER_IP.':8443';
            }
            return 'http://' . self::VIP_SERVER_IP.':8080';
        }

        if ($https) {
            return  'http://' . self::TEST_SERVER_IP.':8443';
        } else {
            return  'http://' . self::TEST_SERVER_IP.':8080';
        }

    }


}
