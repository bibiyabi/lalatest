<?php

namespace App\Http\Controllers;

use App\Models\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Contracts\ResponseCode as CODE;

class KeysController extends Controller
{
    private $keys;

    public function __construct()
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function index()
//    {
//
//    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function create()
//    {
//        //
//    }

    /**
     * Setting payment keys from java.
     * @param Request $request
     *
     */
    public function store(Request $request)
    {
        $rules = [
            "id"                => "required|integer",
            "msgName"           => "nullable|string",
            "bankName"          => "nullable|string",
            "secondName"        => "nullable|string",
            "firstName"         => "nullable|string",
            "cardNumber"        => "nullable|string",
            "ifsc"              => "nullable|string",
            "cashflowMerchant"  => "required|integer",
            "cashflowUserId"    => "nullable|string",
            "cashflowMerchantId"=> "nullable|string",
            "md5"               => "nullable|string",
            "publickey"         => "nullable|string",
            "privatekey"        => "nullable|string",
            "syncAddress"       => "nullable|string",
            "asyncAddress"      => "nullable|string",
            "blockChain"        => "nullable|string",
            "rechargeAdd"       => "nullable|string",
            "apiKey"            => "nullable|string",
            "blockPrivateKey"   => "nullable|string",
            "remark1"           => "nullable|string",
            "remark2"           => "nullable|string",
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $validator->errors()->all(),
            ];
            Log::info(json_encode($errMsg));

            return RB::error(CODE::ERROR_PARAMETERS);
        }

        # store into keys table
        try {
            $key = new Key();
            $key->user_id = $request->user()->id;
            $key->gateway_id = $request->input('cashflowMerchant');
            $key->user_pk = $request->input('id');
            $key->keys = json_encode($request->all());
            $key->save();

        }catch (\Exception $e){
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $e->getMessage(),
            ];
            Log::info(json_encode($errMsg));

            return RB::error(CODE::FAIL);
        }

        return RB::success();
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
