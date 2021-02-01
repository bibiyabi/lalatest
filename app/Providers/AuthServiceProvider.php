<?php

namespace App\Providers;

use App\Contracts\Auth\CacheUserProvider;
use Auth;
use App\Contracts\Auth\UsernameGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
