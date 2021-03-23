<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


use App\Contracts\Payments\PaymentInterface;
use App\Services\Payments\Withdraw\PaymentService;
use App\Services\Payments\Deposit\DepositService;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Services\Payments\Gateways\Inrusdt;
use Illuminate\Contracts\Support\DeferrableProvider;

class PaymentServiceProvider extends ServiceProvider implements DeferrableProvider
{


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            PaymentInterface::class,
            PaymentService::class
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

    public function provides()
    {
        return [PaymentInterface::class];
    }
}
