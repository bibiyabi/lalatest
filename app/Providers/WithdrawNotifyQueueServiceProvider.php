<?php

namespace App\Providers;

use App\Repositories\GatewayRepository;
use App\Exceptions\WithdrawException;
use App\Repositories\SettingRepository;
use App\Services\Payments\PlatformNotify;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Orders\WithdrawRepository;
use App\Services\AbstractWithdrawGateway;
use Exception;

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
    public function boot(PlatformNotify $platformNotify)
    {
        $this->app->bindMethod([Notify::class, 'handle'], function ($job, $app)
        use ($platformNotify) {
            return $job->handle($app->make(Notify::class), $platformNotify);
        });
    }
}
