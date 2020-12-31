<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KeyController extends Controller
{
    /**
     * Setting payment keys from java.
     * @param Request $request
     */
    public function index(Request $request)
    {
        $rules = [
                "id"                => "required|integer",
                "type"              => "required|integer|between:0,2",
                "msgName"           => "required|string",
                "bankName"          => "present",
                "secondName"        => "present",
                "firstName"         => "present",
                "cardNumber"        => "present",
                "ifsc"              => "present",
                "cashflowMerchant"  => "required|string",
                "cashflowUserId"    => "required|string",
                "cashflowMerchantId"=> "required|string",
                "md5"               => "present",
                "publickey"         => "present",
                "privatekey"        => "present",
                "syncAddress"       => "required|string",
                "asyncAddress"      => "required|string",
                "channelId"         => "required|integer",
                "remark1"           => "present",
                "remark2"           => "present",
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $validator->errors()->all(),
            ];
            Log::info(json_encode($errMsg));

            return response()->json(config('code.E02'));
        }



    }
}
