<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Jobs\Payment\Withdraw\Order;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') == 'local'){




            DB::listen(function($query){

                $i = 0;
                $bindings = $query->bindings;
                $rawSql = preg_replace_callback('/\?/', function ($matches) use ($bindings, &$i) {
                    $item = isset($bindings[$i]) ? $bindings[$i] : $matches[0];
                    $i++;
                    return gettype($item) == 'string' ? "'$item'" : $item;
                }, $query->sql);

                Log::info(
                    $rawSql,
                    [$query->time]
                );
            });
        }

        if ($this->app->request->get('debuglog')){
            Log::info(
                $this->app->request->header(),
                $this->app->request->all()
            );
        }
    }
}
