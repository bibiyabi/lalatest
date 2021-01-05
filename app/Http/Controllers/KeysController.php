<?php

namespace App\Http\Controllers;

use App\Models\Keys;
use Illuminate\Http\Request;

class KeysController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Setting payment keys from java.
     * @param Request $request
     *
     */
    public function store(Request $request)
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
            echo 123;
            return ResponseBuilder::success();
//            return response()->json(config('code.E02'));  //todo response改寫法
        }

        // todo stored into DB  /eloquent

        // todo response success
        echo 'success';
        return ResponseBuilder::success();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Keys  $keys
     * @return \Illuminate\Http\Response
     */
    public function show(Keys $keys)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Keys  $keys
     * @return \Illuminate\Http\Response
     */
    public function edit(Keys $keys)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Keys  $keys
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Keys $keys)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Keys  $keys
     * @return \Illuminate\Http\Response
     */
    public function destroy(Keys $keys)
    {
        //
    }
}
