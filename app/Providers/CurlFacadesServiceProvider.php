<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CurlFacadesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('curl', function () {
            return new \App\Lib\Curl\Curl;
        });
    }
}
