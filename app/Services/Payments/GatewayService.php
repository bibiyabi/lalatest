<?php


namespace App\Services\Payments;

use App\Constants\Payments\ResponseCode as CODE;
use App\Constants\Payments\Type;
use App\Repositories\GatewayTypeRepository;
use Illuminate\Http\Request;
use App\Contracts\Payments\ServiceResult;

class GatewayService
{
    protected $gateTypeRepo;

    public function __construct(GatewayTypeRepository $gateTypeRepo)
    {
        $this->gateTypeRepo = $gateTypeRepo;
    }

    public function getGatewayList(Request $request)
    {
        if (array_key_exists($request->input('type'), Type::type)) {
            $type = Type::type[$request->input('type')];
        }else{
            return new ServiceResult(false,CODE::ERROR_CONFIG_PARAMETERS);
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

        $result = $this->gateTypeRepo->getGatewayList($type, $support);
        if (!empty($result)){
            foreach ($result as $key => $value){
                $result[$key] = (array)$value;
            }
            $resultEncode = urlencode(json_encode($result));
            return new ServiceResult(true,CODE::SUCCESS, $resultEncode);
        }else{
            return new ServiceResult(true, CODE::RESOURCE_NOT_FOUND);
        }
    }

    public function getPlaceholder()
    {

    }

    public function getRequireInfo()
    {

    }

}
