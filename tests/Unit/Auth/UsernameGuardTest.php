<?php

namespace Tests\Unit\Auth;

use App\Lib\Auth\CacheUserProvider;
use App\Lib\Auth\UsernameGuard;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class UsernameGuardTest extends TestCase
{
    /** @var UsernameGuard */
    public $service;

    public function setUp(): void
    {
        parent::setUp();
        $request = Request::create('aaa');
        $request->headers->set('name', 'java');
        $this->service = $this->app->make(UsernameGuard::class, [
            'driver' => 'name',
            'provider' => $this->app->make(CacheUserProvider::class, [
                'model' => \App\Models\Merchant::class,
            ]),
            'inputKey' => 'name',
            'storageKey' => 'name',
            'request' => $request,
        ]);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_username()
    {
        $rs = $this->service->getTokenForRequest();
        $this->assertEquals('java', $rs);
    }
}
