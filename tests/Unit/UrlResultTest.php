<?php

namespace Tests\Unit;

use App\Contracts\Payments\HttpParam;
use App\Contracts\Payments\Results\UrlResult;
use App\Exceptions\TpartyException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UrlResultTest extends TestCase
{
    public function test_post()
    {
        Http::fake(['foo.com' => Http::response('success')]);

        $service = new UrlResult();
        $rs = $service->getResult(new HttpParam('foo.com','post',[],[],[]));

        $this->assertEquals('success', $rs->getContent());
        $this->assertEquals('url', $rs->getType());
    }

    public function test_form()
    {
        Http::fake(['foo.com' => Http::response('success')]);

        $service = new UrlResult();
        $rs = $service->getResult(new HttpParam('foo.com','form',[],[],[]));

        $this->assertEquals('success', $rs->getContent());
        $this->assertEquals('url', $rs->getType());
    }

    public function test_get()
    {
        Http::fake(['foo.com' => Http::response('success')]);

        $service = new UrlResult();
        $rs = $service->getResult(new HttpParam('foo.com','get',[],[],[]));

        $this->assertEquals('success', $rs->getContent());
        $this->assertEquals('url', $rs->getType());
    }

    public function test_exception()
    {
        $this->expectException(\Exception::class);

        Http::fake(['foo.com' => Http::response('success')]);

        $service = new UrlResult();
        $service->getResult(new HttpParam('foo.com','asdf',[],[],[]));
    }

    public function test_undefiended_host()
    {
        $this->expectException(TpartyException::class);

        $service = new UrlResult();
        $service->getResult(new HttpParam('fooasdf45你好.com','post',[],[],[]));
    }
}
