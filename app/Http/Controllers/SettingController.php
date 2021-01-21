<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Constants\Payments\ResponseCode as CODE;
use App\Repositories\SettingRepository;

class SettingController extends Controller
{
    protected $repo;

    public function __construct(SettingRepository $repo)
    {
        $this->repo = $repo;
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
            Log::info(json_encode($validator->errors()->all()), $request->post());
            return RB::error(CODE::ERROR_PARAMETERS);
        }
        $settingId = $this->repo->getIdByUserPk($data['id']);

        try {
            if (empty($settingId[0]->id)){
                # create
                $this->repo->insertSetting($request->user()->id,$data['gateway_id'],$data['id'],$dataJson);
            }else{
                # update
                $this->repo->updateSetting($settingId[0]->id, $data['gateway_id'], $dataJson);
            }
        }catch (\Throwable $e){
            Log::info($e->getMessage(), $request->post());
            return RB::error(CODE::FAIL);
        }

        return RB::success(null,CODE::SUCCESS);
    }

    public function destroy(Request $request)
    {
        $rules = ['id' => 'required|integer'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()){
            Log::info(json_encode($validator->errors()->all()), $request->post());
            return RB::error(CODE::ERROR_PARAMETERS);
        }

        try{
            $settingId = $this->repo->getId($request->input('id'));
            if (empty($settingId[0]->id)){
                return RB::error(CODE::FAIL);
            }

            $this->repo->deleteSetting($settingId[0]->id);
        }catch (\Throwable $e){
            Log::info($e->getMessage(), $request->post());
            return RB::error(CODE::FAIL);
        }

        return RB::success(null,CODE::SUCCESS);
    }
}
