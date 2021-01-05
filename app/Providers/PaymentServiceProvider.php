<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AbstractDepositPayment;
use Illuminate\Http\Request;
use App\Repositories\KeysRepository;

use Illuminate\Support\Facades\DB;
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
        # fack request user pk

        $user_pk = '1';
        $a =  $keysRepository->getKeysByUserPk($user_pk);
        $merchant = 'applePay';
        echo __LINE__ ."\r\n";
        $this->app->bind(
            AbstractDepositPayment::class,
            function() use ($merchant) {
                $className = 'App\Payment\Withdraw\/' . $merchant;
                return new $className;
            }
        );
    }
}
