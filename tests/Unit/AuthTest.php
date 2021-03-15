<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Lib\Auth\CacheUserProvider;
use App\Models\Merchant;
use Illuminate\Support\Facades\Cache;

class AuthTest extends TestCase
{
    /** @var CacheUserProvider */
    public $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = $this->app->make(CacheUserProvider::class, [
            'model' => \App\Models\Merchant::class,
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_can_get_user()
    {
        $fakeUser = Merchant::factory()->make();
        Cache::shouldReceive('get')
            ->once()
            ->with('user.name.'.$fakeUser->name)
            ->andReturn($fakeUser);

        $rs = $this->provider->retrieveByCredentials(['name'=>$fakeUser->name]);
        $this->assertEquals($fakeUser, $rs);
    }
}
