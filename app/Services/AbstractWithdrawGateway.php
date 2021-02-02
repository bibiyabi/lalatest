<?php
namespace App\Services;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\Withdraw\WithdrawRequireInfo;
use Illuminate\Http\Request;
use App\Payment\Curl;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Contracts\LogLine;
use App\Services\AbstractWithdrawCallback;
use App\Services\Payments\ResultTrait;
use App\Exceptions\WithdrawException;
use App\Constants\Payments\ResponseCode;
use App\Exceptions\InputException;
use App\Exceptions\DecodeException;
use App\Constants\Payments\Status;

abstract class AbstractWithdrawGateway extends AbstractWithdrawCallback
{
    use ResultTrait;
    # url object
    protected $curl;
    # 回調網址
    protected $callbackUrl;
    # order modal
    protected $order;
    # 建單sign
    protected $createSign;
    # 送單post data
    protected $createPostData = [];

    protected $domain = '';
    protected $createSegments = '';


    public function __construct($curl) {
        $this->curl = $curl;
    }

    protected function setBaseRequest($order, $post):void {
        # 設定model
        $this->setOrder($order);
        # 驗證輸入
        $this->validateOrderInput($post);
        # decode
        $settings = $this->decode($order->key->settings);
        # 建立sign
        $this->setCreateSign($post, $settings);
        # set create order post data
        $this->setCreatePostData($post,  $settings);
    }

    # 設定回調網址
    protected function setCallBackUrl($class) {
        $this->callbackUrl = config('app.url') . '/callback/withdraw/'. class_basename($class);
    }
    # 設定request
    abstract  public function setRequest($post = [], WithdrawOrder $order);
    # 檢查input
    abstract protected function validationCreateInput();
    # 設定發送sign
    abstract protected function setCreateSign($post, $settings);
    # 設定送單array
    abstract protected function setCreatePostData($post, $settings);
    # 設定header
    abstract protected function getCurlHeader();
    # 是否用https 有ture 沒有false
    abstract protected function isHttps();

    # 確認訂單成功狀態
    abstract protected function checkCreateOrderIsSuccess($res);
    # 後端提示字
    abstract public function getPlaceholder($type):Placeholder;
    # 前端提示字
    abstract public function getRequireInfo($type):WithdrawRequireInfo;


    # 確認訂單參數
    protected function validateOrderInput($data) {
        $validator = Validator::make($data, $this->validationCreateInput());
        if ($validator->fails()) {
            throw new InputException($validator->errors(), Status::ORDER_FAILED);
        }
    }

    # 取得建單狀態
    protected function getSendReturn($curlRes) {

        switch ($curlRes['code']) {
            case Curl::STATUS_SUCCESS:
                return $this->returnCreateRes($this->getCreateOrderRes($curlRes));
            case Curl::FAILED:
                return $this->resCreateFailed('', ['order_id' => $this->order->order_id]);
            case Curl::TIMEOUT:
                return $this->resCreateError('', ['order_id' => $this->order->order_id]);
            default:
                throw new WithdrawException("curl rescode default " , Status::ORDER_FAILED);
        }
    }

    protected function getCreateOrderRes($curlRes) {
        return $this->decode($curlRes['data'], true);
    }

    # curl 取得建單狀態
    protected function returnCreateRes($createRes) {
        if ($this->checkCreateOrderIsSuccess($createRes)) {
            return $this->resCreateSuccess('', ['order_id' => $this->order->order_id]);
        } else {
            return $this->resCreateFailed('', ['order_id' => $this->order->order_id]);
        }
    }

    # set order object
    private function setOrder($order) {
        if (empty($order)) {
            throw new WithdrawException('setting empty ', Status::ORDER_FAILED);
        }
        $this->order = $order;
    }

    protected function getCreateSign() {
        return $this->createSign;
    }

    protected function getCreateUrl() {
        $http = ($this->isHttps()) ? 'https://' : 'http://';
        return  $http . $this->domain. $this->createSegments;
    }


    public function send() {

        if (empty($this->getCreatePostData())) {
            throw new WithdrawException('createPostData empty ', Status::ORDER_FAILED);
        }

        $url = $this->getCreateUrl();

        if ($this->isHttps()) {
            $this->curl->ssl();
        }

        $curlRes = $this->curl
        ->setUrl($url)
        ->setHeader($this->getCurlHeader())
        ->setPost($this->getCreatePostData())
        ->exec();

        Log::channel('withdraw')->info(new LogLine('CURL result' . print_r($curlRes, true)));
        Log::channel('withdraw')->info(new LogLine('CURL createPostData '. print_r($this->getCreatePostData(), true)));

        return $this->getSendReturn($curlRes);
    }

    protected function getCreatePostData() {
        return $this->createPostData;
    }

    protected function getSettings($order) {
        $key = $order->key;

        if (empty($key)) {
            throw new WithdrawException("key not found." , Status::ORDER_FAILED);
        }

        return $this->decode($key->settings);
    }

    # for decode
    protected function decode($data) {
        $decode =  json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DecodeException(json_last_error() . 'decode error '. $data, Status::ORDER_ERROR);
        }
        return $decode;
    }

}
