<?php
namespace App\Services;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\WithdrawRequireInfo;
use Illuminate\Http\Request;

abstract class AbstractWithdrawGateway
{


    public function __construct() {

    }

    abstract public function setRequest($data = [], $setting = []);

    abstract public function send() ;

    abstract public function getPlaceholder($type):Placeholder;

    abstract public function getRequireInfo($type):WithdrawRequireInfo;

    public function __get($attribute)
    {
        if(property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        return null;
    }



}
