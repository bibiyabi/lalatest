<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\JavaApiKey;
use App\Lib\Hash\Signature;
use App\Models\Merchant;
use App\Repositories\MerchantRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Mockery\MockInterface;
use Tests\TestCase;

class JavaApiKeyTest extends TestCase
{
    use DatabaseTransactions;


    public function setUp():void
    {
        parent::setUp();
    }

    public function test_handle_same_sign()
    {
        $user = Merchant::factory([
            'name' => 'java',
        ])->create();

        $this->actingAs($user);

        Config::set('app.is_check_sign', true);

        $request = Request::create('/api/withdraw/create', 'POST', ['sign' => 'aaa']);

        $mockMerchantRepo = $this->mock(MerchantRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getKey')->once()->andReturn('');
        });

        $mockSignature = $this->mock(Signature::class, function (MockInterface $mock) {
            $mock->shouldReceive('makeSign')->once()->andReturn('aaa');
        });

        $middleware = new JavaApiKey($mockMerchantRepo, $mockSignature);

        $response = $middleware->handle($request, function () {
            return 'next';
        });

        $this->assertEquals($response, 'next');
    }


    public function test_handle_different_sign()
    {
        $user = Merchant::factory([
            'name' => 'java',
        ])->create();

        $this->actingAs($user);

        Config::set('app.is_check_sign', true);

        $request = Request::create('/api/withdraw/create', 'POST', ['sign' => 'ccc']);

        $mockMerchantRepo = $this->mock(MerchantRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getKey')->once()->andReturn('');
        });

        $mockSignature = $this->mock(Signature::class, function (MockInterface $mock) {
            $mock->shouldReceive('makeSign')->once()->andReturn('aaa');
        });

        $middleware = new JavaApiKey($mockMerchantRepo, $mockSignature);

        $response = $middleware->handle($request, function () {
            return 'next';
        });
        $this->assertEquals($response->original['code'], 159);
    }


    public function test_handle_success_when_is_check_sign_config_is_false()
    {
        $user = Merchant::factory([
            'name' => 'java',
        ])->create();

        $this->actingAs($user);

        Config::set('app.is_check_sign', false);

        $request = Request::create('/api/withdraw/create', 'POST', ['sign' => 'ccc']);

        $mockMerchantRepo = $this->mock(MerchantRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getKey')->once()->andReturn('');
        });

        $mockSignature = $this->mock(Signature::class, function (MockInterface $mock) {
            $mock->shouldReceive('makeSign')->once()->andReturn('aaa');
        });

        $middleware = new JavaApiKey($mockMerchantRepo, $mockSignature);

        $response = $middleware->handle($request, function () {
            return 'next';
        });
        $this->assertEquals($response, 'next');
    }
}
