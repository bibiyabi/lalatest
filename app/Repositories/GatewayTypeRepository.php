<?php


namespace App\Repositories;
use App\Models\Gateway_type;

class GatewayTypeRepository
{
    protected $gatewayType;

    public function __construct(Gateway_type $gatewayType)
    {
        $this->gatewayType = $gatewayType;
    }

    public function getGatewayList($type, $support)
    {
        return $this->gatewayType
                    ->leftJoin('gateways','gateway_types.gateways_id', '=', 'gateways.id')
                    ->select('gateways.id','gateways.name')
                    ->where('types_id', $type)
                    ->where($support,1)
                    ->get()
                    ->toArray();
    }
}
