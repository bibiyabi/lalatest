<?php

namespace App\Http\Controllers;

use App\Contracts\Payments\Deposit\DepositGatewayFactory;
use App\Constants\Payments\ResponseCode as CODE;
use App\Contracts\Payments\WithdrawGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;


class GatewayController extends Controller
{
    # 金流商/交易所下拉選單
    public function index(Request $request)
    {
        $rules = [
            'is_deposit'    => 'required|integer|between:0,1',
            'type'          => 'required|string',
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

        $type = 0;
        if (array_key_exists($request->input('type'), config('params.type'))) {
            $type = config('params.type')[$request->input('type')];
        }else{
            return RB::error(CODE::ERROR_CONFIG_PARAMETERS);
        }

        $support = '';
        switch ($request->input('is_deposit')){
            case 1:
                $support = 'is_support_deposit';
                break;
            case 0:
                $support = 'is_support_withdraw';
                break;
            default:
                break;
        }

        #gateway_type join gateways
        $result = DB::table('gateway_types')
            ->leftJoin('gateways','gateway_types.gateways_id', '=', 'gateways.id')
            ->select('gateways.id','gateways.name')
            ->where('types_id', $type)
            ->where($support,1)
            ->get()->toArray();

        if (!empty($result)){
            foreach ($result as $key => $value){
                $result[$key] = (array)$value;
            }
        }else{
            return RB::success([],CODE::RESOURCE_NOT_FOUND);
        }

        $resultEncode = urlencode(json_encode($result));

        return RB::success($resultEncode,CODE::SUCCESS);
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
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $validator->errors()->all(),
            ];
            Log::info(json_encode($errMsg));

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
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $e->getMessage(),
            ];
            Log::info(json_encode($errMsg));

            return RB::error(CODE::ERROR_DATA_IN_PAYMENT);
        }

        $resultEncode = urlencode(json_encode($result));

        return RB::success($resultEncode,CODE::SUCCESS);

    }

    # 前台的提示字
    public function getRequireInfo(Request $request)
    {
        $rules = [
            'is_deposit'    => 'required|integer|between:0,1',
            'type'          => 'required|string',
            'gateway_name'  => 'required|string',
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

        $gatewayName = $request->input('gateway_name');


        try{
            if ($request->input('is_deposit') == 1){# for deposit
                $gateway = DepositGatewayFactory::createGateway($gatewayName);
            }else{# for withdraw
                $gateway = WithdrawGatewayFactory::createGateway($gatewayName);
            }
        }catch(\Throwable $e){
            $errMsg = [
                'errorPath' => self::class,
                'msg'       => $e->getMessage(),
            ];
            Log::info(json_encode($errMsg));

            return RB::error(CODE::ERROR_DATA_IN_PAYMENT);
        }
        $info = $gateway->getRequireInfo($request->input('type'));
        $result = $info->toArray();

        $resultEncode = urlencode(json_encode($result));
        return RB::success($resultEncode,CODE::SUCCESS);

    }
}
