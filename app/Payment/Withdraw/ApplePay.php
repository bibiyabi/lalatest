<?php
namespace App\Payment\Withdraw;
use App\Services\AbstractDepositPayment;
use App\Validations\ApplyPayValidation;
use App\Exceptions\WithdrawException;
use App\Collections\ApplePayCollection;
class ApplePay extends AbstractDepositPayment
{
    private $requestCollection;

    public function __construct(ApplePayCollection $applePayCollection) {
        echo __CLASS__.__FUNCTION__;
        $this->requestCollection = $applePayCollection;
    }

    public function setRequest($request) {
        $validator = ApplyPayValidation::inputValidator($request);
        if ($validator->fails()) {
            throw new WithdrawException($validator->getMessageBag());
        }

        $this->requestCollection->setData($request)->setPostData()->setSignKey();

        return $this;
    }

    public function send() {

    }

    public function getOrderRes() {
        echo 'apple getOrderRes';
    }

    public function getRedirectType() {
        return 'curl';
    }
}
