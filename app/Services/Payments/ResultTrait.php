<?php
namespace App\Services\Payments;

use App\Constants\Payments\Status;

trait ResultTrait
{
    private $createSuccess = Status::ORDER_SUCCESS;
    private $createTimeout = Status::ORDER_ERROR;
    private $createFailed = Status::ORDER_FAILED;
    private $createRetry = Status::ORDER_ERROR;




    public function resCreateSuccess($msg ='', $data = []) {
        return collect(['code'=> $this->createSuccess, 'msg'=>$msg, 'data' => $data]);
    }

    public function resCreateError($msg ='',$data = []) {
        return collect(['code'=> $this->createTimeout, 'msg'=>$msg, 'data' => $data]);
    }

    public function resCreateFailed($msg ='',$data = []) {
        return collect(['code'=> $this->createFailed, 'msg'=>$msg,'data' => $data]);
    }

    public function resCreateTimeout($msg ='',$data = []) {
        return collect(['code'=> $this->createRetry, 'msg'=>$msg,'data' => $data]);
    }



}
