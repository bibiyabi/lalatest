<?php

namespace Tests\Unit\Payments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Payment\Curl;

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
}
