<?php


namespace App\Repositories;

use App\Models\GatewayType;
use Illuminate\Database\Eloquent\Model;

class GatewayTypeRepository
{
    /**
     * @var $gatewayType Model
     */
    protected $gatewayType;

    public function __construct()
    {
        $this->gatewayType = GatewayType::query();
    }

    public function getGatewayList($type, $support) : array
    {
        $list = $this->gatewayType
                     ->with('gateway')
                     ->where('types_id', $type)
                     ->where($support,1)
                     ->get();

        $result = [];
        foreach ($list as $key => $value)
        {
            $result[$key]['id'] = $value->gateway->id;
            $result[$key]['name'] = $value->gateway->name;
        }

        return $result;
    }
}
