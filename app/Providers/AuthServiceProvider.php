<?php

namespace App\Providers;

use App\Lib\Auth\CacheUserProvider;
use App\Lib\Auth\UsernameGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider(('cache'), function ($app, array $config) {
            return resolve(CacheUserProvider::class, ['model'=>$config['model']]);
        });

        Auth::extend('name', function ($app, $name, array $config) {
            return new UsernameGuard(Auth::createUserProvider($config['provider']), $app->request, $config['inputKey'], $config['storageKey']);
        });
    }
}
