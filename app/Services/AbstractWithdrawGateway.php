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
    protected $curl;
    protected $callbackUrl;

    public function __construct($curl) {
        $this->setCallBackUrl();
        $this->curl = $curl;
    }

    abstract  public function setRequest($post = [], WithdrawOrder $order);



    abstract public function send() ;

    abstract public function getPlaceholder($type):Placeholder;

    abstract public function getRequireInfo($type):WithdrawRequireInfo;


    abstract protected function getNeedValidateParams();
    abstract protected function getCallbackValidateColumns();
    /*
    public function do () {
        $this->validationInput($this->post);
        $params = $this->setRequest($this->post, $this->order);
        $settings = $this->decodeSettings($this->order->setting);
        $this->createSendData($params, $settings);

    }
    */

    protected function decode($data) {
        return json_decode($data, true);
    }

    protected function setCallBackUrl() {
        $this->callbackUrl = config('app.url') . '/withdraw/callback/'. __CLASS__;
    }

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

    protected function getOrderRes($curlRes) {
        $resData = $this->decode($curlRes['data']);
        if ($this->checkOrderIsSuccess($resData)) {
            return $this->resCreateSuccess('', ['order_id' => $this->order->order_id]);
        } else {
            return $this->resCreateFailed('', ['order_id' => $this->order->order_id]);
        }
    }

    protected function validateInput($data) {
        $validator = Validator::make($data, $this->getNeedValidateParams());
        if ($validator->fails()) {
            throw new WithdrawException($validator->errors(), ResponseCode::ERROR_PARAMETERS);
        }
    }

    protected function validateCallbackInput($post) {
        $validator = Validator::make($post, $this->getCallbackValidateColumns());
        if($validator->fails()){
            throw new WithdrawException('callback input check error'. json_encode($validator->errors()), ResponseCode::EXCEPTION);
        }
    }





}
