<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;


use App\Contracts\Payments\PaymentInterface;
use App\Payment\Withdraw\Payment;

use App\Services\AbstractWithdrawGateway;

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

        $this->app->when(DepositService::class)
            ->needs(DepositGatewayInterface::class)
            ->give(function () {
                return new Inrusdt();
            });

        /*
        # fack request user pk
        echo __LINE__;
        $user_pk = '1';
        $a =  $keysRepository->getKeysByUserPk($user_pk);
        $merchant = 'ApplePay';
        echo __LINE__ ."\r\n";
        $this->app->bind(
            AbstractDepositPayment::class,
            function() use ($merchant) {
                $className = '/App/Payment/Withdraw\/' . $merchant;
                return new $className;
            }
        );
        */
    }
}
