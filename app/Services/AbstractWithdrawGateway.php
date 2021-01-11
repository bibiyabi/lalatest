<?php
namespace App\Services;
use Illuminate\Http\Request;
use App\Services\InputService;
use App\Collections\ApplePayCollection;
use App\Collections\BanksCollection;
use App\Collections\ApplePayBanksCollection;
abstract class AbstractWithdrawGateway
{


    public function __construct() {

    }

    abstract public function setRequest($data);

    abstract public function send() ;

    public function __get($attribute)
    {
        if(property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        return null;
    }



}
