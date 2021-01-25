<?php


namespace App\Services\Payments;


use App\Constants\Payments\ResponseCode as CODE;
use App\Contracts\Payments\ServiceResult;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Log;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class SettingService
{
    protected $repo;

    public function __construct(SettingRepository $repository)
    {
        $this->repo = $repository;
    }

    public function createSetting($userId, $data)
    {
        $settingId = $this->repo->getIdByUserPk($data['id'],$userId)->toArray();

        try {
            if (empty($settingId)){
                # create
                $this->repo->insertSetting($userId, $data);
            }else{
                # update
                $this->repo->updateSetting($settingId[0]['id'], $data);
            }
        }catch (\Throwable $e){
            Log::info($e->getMessage().' PATH: '.__METHOD__, $data);
            return new ServiceResult(false, CODE::FAIL);
        }
        return new ServiceResult(true, CODE::SUCCESS);
    }

    public function deleteSetting($userId, $request)
    {
        try{
            $settingId = $this->repo->getIdByUserPk($request->input('id'),$userId);
            if (empty($settingId)){
                return new ServiceResult(false, CODE::FAIL);
            }
            $this->repo->deleteSetting($settingId[0]['id']);
        }catch (\Throwable $e){
            Log::info($e->getMessage().' PATH: '.__METHOD__, $request->post());
            return new ServiceResult(false, CODE::FAIL);
        }
        return new ServiceResult(true, CODE::SUCCESS);
    }
}
