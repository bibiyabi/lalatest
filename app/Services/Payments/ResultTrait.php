<?php
namespace App\Services\Payments;

trait ResultTrait
{
    private $success = 1;
    private $timeout = 2;
    private $failed = 4;

    public function resSuccess($data = []) {
        return ['code'=> $this->success, 'msg'=>'', 'data' => $data];
    }

    public function resRetry($data = []) {
        return ['code'=> $this->success, 'msg'=>'', 'data' => $data];
    }

    public function resFailed($data = []) {
        return ['code'=> $this->success, 'msg'=>'','data' => $data];
    }


}
