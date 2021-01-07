<?php

namespace App\Providers;

use App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use App\Repositories\KeysRepository;
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

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(KeysRepository $keysRepository)
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
