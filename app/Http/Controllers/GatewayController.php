<?php

namespace App\Http\Controllers;

use App\Constants\Payments\ResponseCode as CODE;
use App\Services\Payments\GatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class GatewayController extends Controller
{
    protected $service;

    public function __construct(GatewayService $service)
    {
        $this->service = $service;
    }

    # 金流商/交易所下拉選單
    public function index(Request $request)
    {
        $rules = [
            'is_deposit'    => 'required|integer|between:0,1',
            'type'          => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()){
            Log::info(json_encode($validator->errors()->all()), $request->post());
            return RB::error(CODE::ERROR_PARAMETERS);
        }
        $result = $this->service->getGatewayList($request);

        return $result->getSuccess()
            ? RB::success($result->getResult(), $result->getErrorCode())
            : RB::error($result->getErrorCode());
    }

    # 提示字
    public function getPlaceholder(Request $request)
    {
        $rules = [
            'is_deposit'    => 'required|integer|between:0,1',
            'type'          => 'required|string',
            'gateway_name'  => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()){
            Log::info(json_encode($validator->errors()->all()), $request->post());
            return RB::error(CODE::ERROR_PARAMETERS);
        }

        $result = $this->service->getPlaceholder($request);

        return $result->getSuccess()
            ? RB::success($result->getResult(), $result->getErrorCode())
            : RB::error($result->getErrorCode());
    }

    # 前台的出/入款應顯示欄位及下拉選單
    public function getRequireInfo(Request $request)
    {
        $rules = [
            'is_deposit'    => 'required|integer|between:0,1',
            'type'          => 'required|string',
            'gateway_name'  => 'required|string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()){
            Log::info(json_encode($validator->errors()->all()), $request->post());
            return RB::error(CODE::ERROR_PARAMETERS);
        }
        $result = $this->service->getRequireInfo($request);

        return $result->getSuccess()
            ? RB::success($result->getResult(), $result->getErrorCode())
            : RB::error($result->getErrorCode());
    }

}
