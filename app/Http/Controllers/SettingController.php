<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Constants\Payments\ResponseCode as CODE;

class SettingController extends Controller
{
    private $keys;


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

    # Setting payment keys from java.
    public function store(Request $request)
    {
        $rules = [
            "id"                  => "required|integer",
            "info_title"          => "nullable|string",
            "gateway_id"          => "required|integer",
            "transaction_type"    => "nullable|string",
            "account"             => "nullable|string",
            "merchant_number"     => "nullable|string",
            "md5_key"             => "nullable|string",
            "public_key"          => "nullable|string",
            "private_key"         => "nullable|string",
            "return_url"          => "nullable|string",
            "notify_url"          => "nullable|string",
            "coin"                => "nullable|string",
            "blockchain_contract" => "nullable|string",
            "crypto_address"      => "nullable|string",
            "api_key"             => "nullable|string",
            "note1"               => "nullable|string",
            "note2"               => "nullable|string",
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
            $key = new Setting();
            $key->user_id = $request->user()->id;
            $key->gateway_id = $request->input('gateway_id');
            $key->user_pk = $request->input('id');
            $key->settings = json_encode($request->all());
            $key->save();

        }catch (\Throwable $e){
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $e->getMessage(),
            ];
            Log::info(json_encode($errMsg));

            return RB::error(CODE::FAIL);
        }

        return RB::success(null,CODE::SUCCESS);
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


    public function destroy(Request $request)
    {
        $rule = [
            ''
        ];
    }


}
