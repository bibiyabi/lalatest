<?php
namespace App\Services\Payments;

use App\Constants\Payments\Status;

trait ResultTrait
{
    private $createSuccess = Status::ORDER_SUCCESS;
    private $createTimeout = Status::ORDER_ERROR;
    private $createFailed = Status::ORDER_FAILED;
    private $createRetry = Status::ORDER_ERROR;

    private $callbackSuccess = Status::CALLBACK_SUCCESS;
    private $callbackFail = \App\Constants\Payments\Status::CALLBACK_FAILED;

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


}
