<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;


use App\Contracts\Payments\PaymentInterface;
use App\Payment\Withdraw\Payment;
use App\Services\Payments\DepositService;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Services\Payments\Gateways\Inrusdt;

class PaymentServiceProvider extends ServiceProvider
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
            Payment::class
        );





    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->app->when(DepositService::class)
            ->needs(DepositGatewayInterface::class)
            ->give(function () {
                return new Inrusdt();
            });


    }
}
