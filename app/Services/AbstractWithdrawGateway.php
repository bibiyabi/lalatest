<?php
namespace App\Services;
use Illuminate\Http\Request;
use App\Services\InputService;

abstract class AbstractWithdrawGateway
{
    private $request;

    public function __construct() {

    }

    abstract public function send() ;



    abstract public function getOrderRes();


}
