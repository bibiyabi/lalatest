<?php

namespace Tests\Unit\Payments;

use App\Facades\Curl as FacadesCurl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Lib\Curl\Curl;

class CurlTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_curl_timeout()
    {
        $curl = new Curl();
        $res = $curl->setUrl('http://10.255.255.1')->exec();

        $this->assertEquals(Curl::TIMEOUT, $res['code']);
    }


    public function test_curl_url_not_connected()
    {
        $curl = new Curl();
        $res = $curl->setUrl('http://ddfddffdf/c')->exec();

        $this->assertEquals(Curl::FAILED, $res['code']);
    }

    public function test_curl_url_success()
    {
        $curl = new Curl();
        $res = $curl->setUrl('https://www.google.com.tw/')->ssl(false)->exec();

        $this->assertEquals(Curl::STATUS_SUCCESS, $res['code']);
    }


    public function test_static_curl_timeout()
    {
        $res = FacadesCurl::setUrl('http://10.255.255.1')->exec();

        $this->assertEquals(Curl::TIMEOUT, $res['code']);
    }


    public function test_static_curl_url_not_connected()
    {
        $res = FacadesCurl::setUrl('http://ddfddffdf/c')->exec();

        $this->assertEquals(Curl::FAILED, $res['code']);
    }

    public function test_static_curl_url_success()
    {
        $res = FacadesCurl::setUrl('https://www.google.com.tw/')->ssl(false)->exec();

        $this->assertEquals(Curl::STATUS_SUCCESS, $res['code']);
    }
}
