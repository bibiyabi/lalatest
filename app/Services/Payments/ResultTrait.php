<?php
namespace App\Services\Payments;

use app\Constants\WithDrawOrderStatus;

trait ResultTrait
{
    private $success = WithDrawOrderStatus::SUCCESS;
    private $timeout = WithDrawOrderStatus::TIMEOUT;
    private $failed = WithDrawOrderStatus::FAIL;
    private $retry = WithDrawOrderStatus::RETRY;

    public function resSuccess($msg ='', $data = []) {
        return collect(['code'=> $this->success, 'msg'=>'', 'data' => $data]);
    }

    public function resRetry($msg ='',$data = []) {
        return collect(['code'=> $this->retry, 'msg'=>'', 'data' => $data]);
    }

    public function resFailed($msg ='',$data = []) {
        return collect(['code'=> $this->failed, 'msg'=>'','data' => $data]);
    }

    public function resTimeout($msg ='',$data = []) {
        return collect(['code'=> $this->retry, 'msg'=>'','data' => $data]);
    }


}
