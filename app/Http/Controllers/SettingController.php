<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Constants\Payments\ResponseCode as CODE;

class SettingController extends Controller
{
    # Setting payment keys from java.
    public function store(Request $request)
    {
        if (empty($request->all())|| empty($request->input('data'))){
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => 'INPUT PARAMS IS EMPTY.',
            ];
            Log::info(json_encode($errMsg));
            return RB::error(CODE::ERROR_PARAMETERS);
        }

        $dataJson = urldecode($request->input('data'));
        $data = json_decode($dataJson, true);
        $data['id'] = $request->input('id');
        $data['gateway_id'] = $request->input('gateway_id');

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
        $validator = Validator::make($data, $rules);

        if ($validator->fails()){
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $validator->errors()->all(),
            ];
            Log::info(json_encode($errMsg));

            return RB::error(CODE::ERROR_PARAMETERS);
        }

        $settingId = DB::table('settings')
            ->select('id')
            ->where('user_pk','=',$data['id'])
            ->get();

        try {
            if (empty($settingId[0]->id)){
                # create
                DB::table('settings')->insert([
                    'user_id'       => $request->user()->id,
                    'gateway_id'    => $data['gateway_id'],
                    'user_pk'       => $data['id'],
                    'settings'      => $dataJson,
                    'created_at'    => date('Y-m-d H:i:s', time()),
                    'updated_at'    => date('Y-m-d H:i:s', time()),
                ]);
            }else{
                # update
                DB::table('settings')->where('id', '=', $settingId[0]->id)
                    ->update([
                        'gateway_id'    => $data['gateway_id'],
                        'settings'      => $dataJson,
                        'updated_at'    => date('Y-m-d H:i:s', time()),
                ]);
            }
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


    # 取得資料
//    public function edit(Request $request)
//    {
//        $rules = [
//            'id' => 'required|integer'
//        ];
//        $validator = Validator::make($request->all(), $rules);
//
//        if ($validator->fails()){
//            $errMsg = [
//                'errorPath' => self::class,
//                'msg'       => $validator->errors()->all(),
//            ];
//            Log::info(json_encode($errMsg));
//
//            return RB::error(CODE::ERROR_PARAMETERS);
//        }
//
//        $settingId = DB::table('settings')
//            ->select('id')
//            ->where('id','=',$request->input('id'))
//            ->get();
//
//        if (empty($settingId->id)){
//            return RB::error(CODE::FAIL);
//        }
//
//        return RB::success(null,CODE::SUCCESS);
//    }

    # 更新設置資料
    public function update(Request $request)
    {
//        $rules = [
//            "setting_id"          => "required|integer",
//            "info_title"          => "nullable|string",
//            "gateway_id"          => "required|integer",
//            "transaction_type"    => "nullable|string",
//            "account"             => "nullable|string",
//            "merchant_number"     => "nullable|string",
//            "md5_key"             => "nullable|string",
//            "public_key"          => "nullable|string",
//            "private_key"         => "nullable|string",
//            "return_url"          => "nullable|string",
//            "notify_url"          => "nullable|string",
//            "coin"                => "nullable|string",
//            "blockchain_contract" => "nullable|string",
//            "crypto_address"      => "nullable|string",
//            "api_key"             => "nullable|string",
//            "note1"               => "nullable|string",
//            "note2"               => "nullable|string",
//        ];
//        $validator = Validator::make($request->all(), $rules);
//
//        if ($validator->fails()){
//            $errMsg = [
//                'errorPath' => self::class,
//                'msg'       => $validator->errors()->all(),
//            ];
//            Log::info(json_encode($errMsg));
//
//            return RB::error(CODE::ERROR_PARAMETERS);
//        }
    }


    public function destroy(Request $request)
    {
        $rules = [
            'id' => 'required|integer'
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

        try{
            $settingId = DB::table('settings')
                ->select('id')
                ->where('user_pk','=',$request->input('id'))
                ->get();

            if (empty($settingId[0]->id)){
                return RB::error(CODE::FAIL);
            }

            DB::table('settings')->where('user_pk','=',$request->input('id'))->delete();
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


}
