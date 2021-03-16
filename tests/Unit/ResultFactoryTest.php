<?php

namespace Tests\Unit;

use App\Lib\Payments\Results\FormResult;
use PHPUnit\Framework\TestCase;
use App\Lib\Payments\Results\ResultFactory;
use App\Lib\Payments\Results\UrlResult;
use Illuminate\Support\Str;

class ResultFactoryTest extends TestCase
{
    public function test_create_url_result()
    {
        $rs = ResultFactory::createResultFactory('url');
        $this->assertTrue($rs instanceof UrlResult);
    }

    public function test_create_form_result()
    {
        $rs = ResultFactory::createResultFactory('form');
        $this->assertTrue($rs instanceof FormResult);
    }

    public function test_create_not_exist_result()
    {
        $this->expectException(\Exception::class);
        ResultFactory::createResultFactory(Str::random(5));
    }
}
