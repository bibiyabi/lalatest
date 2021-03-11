<?php

namespace App\Providers;

use App\Services\Payments\Withdraw\WithdrawNotify;
use Illuminate\Support\ServiceProvider;

class WithdrawNotifyQueueServiceProvider extends ServiceProvider
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
    public function boot(WithdrawNotify $WithdrawNotify)
    {
        $this->app->bindMethod([Notify::class, 'handle'], function ($job, $app)
        use ($WithdrawNotify) {
            return $job->handle($app->make(Notify::class), $WithdrawNotify);
        });
    }
}
