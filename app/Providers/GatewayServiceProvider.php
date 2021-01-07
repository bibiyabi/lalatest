<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AbstractWithdrawGateway;


class GatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $merchant = 'ApplePay';
        $this->app->bind(
            AbstractWithdrawGateway::class,
            function() use ($merchant) {
                $className = "App\Payment\Withdraw\\$merchant";
                return $this->app->make($className);
            }
        );


    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {



    }
}
