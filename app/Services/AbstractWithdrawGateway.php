<?php
namespace App\Services;
use Illuminate\Http\Request;

abstract class AbstractWithdrawGateway
{


    public function __construct() {

    }

    abstract public function setRequest($data = [], $setting = []);

    abstract public function send() ;

    abstract public function getPlaceholder():array;

    public function __get($attribute)
    {
        if(property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
        return null;
    }



}
