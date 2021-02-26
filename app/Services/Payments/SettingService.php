<?php


namespace App\Services\Payments;


use App\Constants\Payments\ResponseCode as CODE;
use App\Contracts\Payments\ServiceResult;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Log;

class SettingService
{
    protected $repo;

    public function __construct(SettingRepository $repository)
    {
        $this->repo = $repository;
    }

    public function createSetting($userId, $data)
    {
        $settingId = $this->repo->filterCombinePk($userId, $data['id'])->first();

        try {
            if (empty($settingId)){ # create
                $this->repo->insertSetting($userId, $data);
            }else{ # update
                $this->repo->updateSetting($settingId->id, $data);
            }
        }catch (\PDOException $e){
            echo $e->getMessage().' PATH: '.__METHOD__;
            Log::info($e->getMessage()."\n".' PATH: '.__METHOD__);
            return new ServiceResult(false, CODE::FAIL);
        }
        return new ServiceResult(true, CODE::SUCCESS);
    }

    public function deleteSetting($userId, $request)
    {
        try{
            $settingId = $this->repo->filterCombinePk($userId, $request->input('id'))->first();
            if (empty($settingId)){
                return new ServiceResult(false, CODE::FAIL);
            }
            $this->repo->deleteSetting($settingId->id);
        }catch (\PDOException $e){
            Log::info($e->getMessage().' PATH: '.__METHOD__, $request->post());
            return new ServiceResult(false, CODE::FAIL);
        }
        return new ServiceResult(true, CODE::SUCCESS);
    }
}
