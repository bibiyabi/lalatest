<?php

namespace App\Http\Controllers;

use App\Common\ExceptionHandler;
use Illuminate\Http\Request;
use Exception;

class TestController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request){
        // todo add verification

        $token = [
            'status' => 1,
            'token'  => $request->input('token'),
        ];

        return response()->json($token);
    }
}
