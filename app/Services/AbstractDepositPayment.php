<?php
namespace App\Services;
use Illuminate\Http\Request;

abstract class AbstractDepositPayment
{
    private $request;

    public function __construct() {

    }

    public function send() {

        if ($this->getRedirectType() == 'form') {
            #return view('ecpay::send', $data);
        } else {
            # curl

        }

    }

    public function setRequest($request) {
        $this->request = $request;
        return $this;
    }

    abstract public function getOrderRes();

    abstract public function getRedirectType();
}
