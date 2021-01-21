<?php
namespace App\Services;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\WithdrawRequireInfo;
use Illuminate\Http\Request;
use App\Payment\Curl;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Validator;

abstract class AbstractWithdrawGateway
{
    // url object
    protected $curl;
    // 回調網址
    protected $callbackUrl;
    // order modal
    protected $order;


    public function __construct($curl) {
        $this->curl = $curl;
    }

    protected function setBaseRequest($order, $post) {
        // set order model
        $this->setOrder($order);
        // check client input
        $this->validateOrderInput($post);
        // set callback url
        $this->setCallBackUrl();
        // get gateway config
        $settings = $this->decode($order->key->settings);
        // create post order sign key
        $this->createSign($post, $settings);
        // set create order post data
        $this->setCreatePostData($post,  $settings);
    }

    // 設定request
    abstract  public function setRequest($post = [], WithdrawOrder $order);
    // 設定送單array
    abstract protected function setCreatePostData($post, $settings);
    // 建單
    abstract public function send();

    // 確認訂單成功狀態
    abstract protected  function checkOrderIsSuccess($res);
    // 後端提示字
    abstract public function getPlaceholder($type):Placeholder;
    // 前端提示字
    abstract public function getRequireInfo($type):WithdrawRequireInfo;
    // callback 驗證變數
    abstract protected function getCallbackValidateColumns();

    // 確認訂單參數
    protected function validateOrderInput($data) {
        $validator = Validator::make($data, $this->validationCreateInput());
        if ($validator->fails()) {
            throw new WithdrawException($validator->errors(), ResponseCode::ERROR_PARAMETERS);
        }
    }

    // for decode
    protected function decode($data) {
        return json_decode($data, true);
    }


    // 取得建單狀態
    protected function getSendReturn($curlRes) {

        switch ($curlRes['code']) {
            case Curl::STATUS_SUCCESS:
                return $this->getOrderRes($curlRes);
            case Curl::FAILED:
                return $this->resCreateFailed('', ['order_id' => $this->order->order_id]);

            case Curl::TIMEOUT:
                return $this->resCreateRetry('', ['order_id' => $this->order->order_id]);
            default:
                throw new WithdrawException("curl rescode default " , ResponseCode::EXCEPTION);
        }
    }

    // curl 取得建單狀態
    protected function getOrderRes($curlRes) {
        $resData = $this->decode($curlRes['data']);
        if ($this->checkOrderIsSuccess($resData)) {
            return $this->resCreateSuccess('', ['order_id' => $this->order->order_id]);
        } else {
            return $this->resCreateFailed('', ['order_id' => $this->order->order_id]);
        }
    }

    // 檢查回調input
    protected function validateCallbackInput($post) {
        $validator = Validator::make($post, $this->getCallbackValidateColumns());
        if($validator->fails()){
            throw new WithdrawException('callback input check error'. json_encode($validator->errors()), ResponseCode::EXCEPTION);
        }
    }




    // set order object
    private function setOrder($order) {
        if (empty($order)) {
            throw new WithdrawException('setting empty ', ResponseCode::ERROR_PARAMETERS);
        }
        $this->order = $order;
    }





}
