<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Closure;

class BeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        // Perform action
        Log::channel('access')->debug('request', ['request' => $request]);
        return $next($request);
    }
}
