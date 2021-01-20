<?php

namespace App\Http\Controllers;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Deposit\DepositGatewayFactory;
use App\Constants\Payments\ResponseCode as CODE;
use App\Contracts\Payments\WithdrawGatewayFactory;
use App\Services\Payments\GatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Repositories\GatewayTypeRepository;

class GatewayController extends Controller
{
    protected $gateTypeRepo;

    public function __construct(GatewayTypeRepository $gateTypeRepo)
    {
        $this->gateTypeRepo = $gateTypeRepo;
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

        $service = App::make(GatewayService::class);
        $result = App::call([$service, 'getGatewayList'], ['request' => $request]);

        return $result->getSuccess() ? RB::success($result->getResult(), $result->getErrorCode())
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

        $gatewayName = $request->input('gateway_name');
        try {
            if ($request->input('is_deposit') == 1){# for deposit
                $gateway = DepositGatewayFactory::createGateway($gatewayName);
            }else{# for withdraw
                $gateway = WithdrawGatewayFactory::createGateway($gatewayName);
            }
            $placeholder = $gateway->getPlaceholder($request->input('type'));
            $result = $placeholder->toArray();

        }catch(\Throwable $e){
            Log::info($e->getMessage(), $request->post());
            return RB::error(CODE::ERROR_DATA_IN_PAYMENT);
        }

        $resultEncode = urlencode(json_encode($result));
        return RB::success($resultEncode,CODE::SUCCESS);
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
        $gatewayName = $request->input('gateway_name');

        try{
            if ($request->input('is_deposit') == 1){# for deposit
                $gateway = DepositGatewayFactory::createGateway($gatewayName);
            }else{# for withdraw
                $gateway = WithdrawGatewayFactory::createGateway($gatewayName);
            }
        }catch(\Throwable $e){
            Log::info($e->getMessage(),$request->post());
            return RB::error(CODE::ERROR_DATA_IN_PAYMENT);
        }
        $info = $gateway->getRequireInfo($request->input('type'));
        $result = $info->toArray();

        $resultEncode = urlencode(json_encode($result));
        return RB::success($resultEncode,CODE::SUCCESS);

    }
}
