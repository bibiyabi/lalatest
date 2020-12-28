<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request){
        // todo add verify

        $token = [
            'status' => 1,
            'token'  => $request->input('token'),
        ];

        return response()->json($token);
    }
}
