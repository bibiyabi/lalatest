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

class WithdrawOrderQueueServiceProvider extends ServiceProvider
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
    public function boot(GatewayRepository $gatewayRepository, SettingRepository $settingRepository)
    {
        $this->app->bindMethod([Order::class, 'handle'], function ($job, $app)
        use ($gatewayRepository, $settingRepository) {

            $request = $job->getRequest();
            $request['gateway_id'] = 3;
            $gateway = $gatewayRepository->filterGatewayId($request['gateway_id'])->first();

            $gateway = collect($gateway);
            if (! $gateway->has('name')) {
                throw new Exception('gateway name not found', 22);
            }
            $gatewayName = $gateway->get('name');

            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            $filePath = app_path(). '\Services\Payments\WithdrawGateways\\' . $gatewayName. '.php';

            if (! file_exists($filePath)) {
                throw new Exception(__LINE__ . $gatewayName . 'gateway not found', 22);
            }

            return $job->handle($app->make($className), $settingRepository);
        });
    }
}
