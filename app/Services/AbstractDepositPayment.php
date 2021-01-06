<?php
namespace App\Services;
use Illuminate\Http\Request;

abstract class AbstractDepositPayment
{
    private $request;

    public function __construct() {

    }

    abstract public function send() ;

    public function setRequest($request) {
        return $this;
    }

    abstract public function getOrderRes();


}
