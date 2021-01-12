<?php
namespace App\Services\Payments;

use App\Constants\WithDrawOrderStatus;

trait ResultTrait
{
    private $createSuccess = WithDrawOrderStatus::CREATE_SUCCESS;
    private $createTimeout = WithDrawOrderStatus::CREATE_TIMEOUT;
    private $createFailed = WithDrawOrderStatus::CREATE_FAIL;
    private $createRetry = WithDrawOrderStatus::CREATE_RETRY;

    private $callbackSuccess = WithDrawOrderStatus::CALLBACK_SUCCESS;
    private $callbackFail = WithDrawOrderStatus::CALLBACK_FAIL;
    private $callbackRetry = WithDrawOrderStatus::CALLBACK_RETRY;

    public function resCreateSuccess($msg ='', $data = []) {
        return collect(['code'=> $this->createSuccess, 'msg'=>'', 'data' => $data]);
    }

    public function resCreateRetry($msg ='',$data = []) {
        return collect(['code'=> $this->createTimeout, 'msg'=>'', 'data' => $data]);
    }

    public function resCreateFailed($msg ='',$data = []) {
        return collect(['code'=> $this->createFailed, 'msg'=>'','data' => $data]);
    }

    public function resCreateTimeout($msg ='',$data = []) {
        return collect(['code'=> $this->createRetry, 'msg'=>'','data' => $data]);
    }


    public function resCallbackSuccess($msg ='', $data = []) {
        return collect(['code'=> $this->callbackSuccess, 'msg'=>'', 'data' => $data]);
    }

    public function resCallbackFailed($msg ='',$data = []) {
        return collect(['code'=> $this->callbackFail, 'msg'=>'', 'data' => $data]);
    }

    public function resCallbackRetry($msg ='',$data = []) {
        return collect(['code'=> $this->callbackRetry, 'msg'=>'','data' => $data]);
    }


}
