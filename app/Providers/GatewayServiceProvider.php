<?php

namespace App\Providers;

use App\Jobs\Payment\Withdraw\Order;

use App\Repositories\KeyRepository;
use App\Repositories\GatewayRepository;
use App\Exceptions\WithdrawException;

use Illuminate\Support\ServiceProvider;



class GatewayServiceProvider extends ServiceProvider
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
    public function boot(KeyRepository $keyRepository, GatewayRepository $gatewayRepository)
    {
        $this->app->bindMethod([Order::class, 'handle'], function ($job, $app) use ($keyRepository, $gatewayRepository) {

            $request = $job->getRequest();
            $keys = $keyRepository->filterCombinePk($request['user_id'], $request['user_pk'])->first();

            if (! collect($keys)->has('gateway_id')) {
                throw new WithdrawException('aaa', 0 );
            }

            echo '@@' . $keys->gateway_id;
            $gateway = $gatewayRepository->filterGatewayId($keys->gateway_id)->first();

            $gateway = collect($gateway);
            if (! $gateway->has('name')) {
                throw new WithdrawException('aaa', 0);
            }

            $gatewayName = $gateway->get('name');
            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            return $job->handle($app->make($className));
        });

    }
}
