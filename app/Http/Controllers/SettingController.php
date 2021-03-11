<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Constants\Payments\ResponseCode as CODE;


class SettingController extends Controller
{
    protected $service;

    public function __construct(SettingService $settingService)
    {
        $this->service = $settingService;
    }

    public function store(Request $request)
    {
        if (empty($request->all())|| empty($request->input('data'))){
            Log::info('INPUT PARAMS IS EMPTY.  '.self::class, $request->post());
            return RB::error(CODE::ERROR_PARAMETERS);
        }

        $dataJson = urldecode($request->input('data'));
        $data = json_decode($dataJson, true);
        $data['id'] = $request->input('id');
        $data['gateway_id'] = $request->input('gateway_id');

        $rules = [
            "id"                  => "required|integer",
            "info_title"          => "nullable|string",
            "gateway_id"          => "required|integer|exists:gateways,id",
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
            Log::info(json_encode($validator->errors()->all()), $data);
            return RB::error(CODE::ERROR_PARAMETERS);
        }
        $result = $this->service->createSetting($request->user()->id, $data);

        return $result->getSuccess()
            ? RB::success($result->getResult(), $result->getErrorCode())
            : RB::error($result->getErrorCode());
    }

    public function destroy(Request $request)
    {
        $rules = ['id' => 'required|integer'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()){
            Log::info(json_encode($validator->errors()->all()), $request->post());
            return RB::error(CODE::ERROR_PARAMETERS);
        }
        $result = $this->service->deleteSetting($request->user()->id,$request);

        return $result->getSuccess()
            ? RB::success($result->getResult(), $result->getErrorCode())
            : RB::error($result->getErrorCode());
    }
}
