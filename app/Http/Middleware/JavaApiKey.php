<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JavaApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $ukey = env('JAVA_API_KEY');

        $input = $request->except('signature');

        $inputSignature = $request->signature;

        ksort($input);

        $sign_str = '';
        foreach ($input as $key => $value) {
            $sign_str  .= $key . '=' . $value . '&';
        }

        $sign_str = substr($sign_str, 0 , -1);


        $signature = md5($sign_str . $ukey);

        if ( $signature !== strtolower($inputSignature)) {

            return Response()->json(['java api key '=>' error ']);
        }

        return $next($request);
    }
}
