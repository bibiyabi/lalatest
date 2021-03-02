<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;


class QueueLogServiceProvider extends ServiceProvider
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
    public function boot()
    {
        /**
        * Log jobs
        *
        * Job dispatched & processing
        */
        Queue::before(function ( JobProcessing $event ) {
            Log::channel('queue')->info($event->job->uuid() . 'Job ready: ' . $event->job->resolveName() . 'Job payload: ' , $event->job->payload());
        });

        /**
        * Log jobs
        *
        * Job processed
        */
        Queue::after(function ( JobProcessed $event ) {
            Log::channel('queue')->notice($event->job->uuid() .'Job done: ' . $event->job->resolveName());
        });

        /**
        * Log jobs
        *
        * Job failed
        */
        Queue::failing(function ( JobFailed $event ) {
            Log::channel('queue')->error($event->job->uuid() .'Job failed: ' . $event->job->resolveName() . '(' . $event->exception->getMessage() . ')');
        });
    }
}
