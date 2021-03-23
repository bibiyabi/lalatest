<?php


namespace App\Services;

use App\Constants\Payments\ResponseCode as CODE;
use App\Constants\Payments\Type;
use App\Lib\Payments\Deposit\DepositGatewayFactory;
use App\Lib\Payments\Withdraw\WithdrawGatewayFactory;
use App\Repositories\GatewayTypeRepository;
use Illuminate\Support\Facades\Log;

class GatewayService
{
    protected $gateTypeRepo;

    public function __construct(GatewayTypeRepository $gateTypeRepo)
    {
        $this->gateTypeRepo = $gateTypeRepo;
    }

    /**
     * @param $request
     * @return ServiceResult
     */
    public function getGatewayList($request)
    {
        if (array_key_exists($request->input('type'), Type::type)) {
            $type = Type::type[$request->input('type')];
        } else {
            return new ServiceResult(false, CODE::ERROR_CONFIG_PARAMETERS);
        }

        $support = '';
        switch ($request->input('is_deposit')) {
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
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $result[$key] = (array)$value;
            }
        }

        $resultEncode = urlencode(json_encode($result));
        return new ServiceResult(true, CODE::SUCCESS, $resultEncode);
    }

    /**
     * @param $request
     * @return ServiceResult
     */
    public function getPlaceholder($request)
    {
        if (!array_key_exists($request->input('type'), Type::type)) {
            return new ServiceResult(false, CODE::ERROR_CONFIG_PARAMETERS);
        }

        $gateway = $this->getFactory($request->input('is_deposit'), $request->input('gateway_name'));
        $result = $gateway->getPlaceholder($request->input('type'))->toArray();

        return new ServiceResult(true, CODE::SUCCESS, urlencode(json_encode($result)));
    }

    /**
     * @param $request
     * @return ServiceResult
     */
    public function getRequireInfo($request)
    {
        if (!array_key_exists($request->input('type'), Type::type)) {
            return new ServiceResult(false, CODE::ERROR_CONFIG_PARAMETERS);
        }

        $gateway = $this->getFactory($request->input('is_deposit'), $request->input('gateway_name'));
        $result = $gateway->getRequireInfo($request->input('type'))->toArray();

        return new ServiceResult(true, CODE::SUCCESS, urlencode(json_encode($result)));
    }

    private function getFactory($isDeposit, $gatewayName)
    {
        if ($isDeposit == 1) {# for deposit
            return DepositGatewayFactory::createGateway($gatewayName);
        } else {# for withdraw
            return WithdrawGatewayFactory::createGateway($gatewayName);
        }
    }
}
