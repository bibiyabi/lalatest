<?php
namespace App\Services;

use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\Withdraw\WithdrawRequireInfo;
use App\Payment\Curl;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Contracts\LogLine;
use App\Exceptions\WithdrawException;
use App\Exceptions\InputException;
use App\Exceptions\DecodeException;
use App\Constants\Payments\Status;
use App\Services\WithdrawCallback;

abstract class AbstractWithdrawGateway
{
    use WithdrawCallback;
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
    # domain 是否有第三方派發, 如果是固定讀settins->note1
    protected $isDomainDynamic = false;

    protected $isCurlProxy = true;

    protected $domain = '';
    protected $createSegments = '';


    public function __construct($curl) {
        $this->curl = $curl;
    }

    public function logRequest($order, $post) {
        Log::channel('withdraw')->info(new LogLine('第三方參數 post ' . json_encode($post)));
        Log::channel('withdraw')->info(new LogLine('第三方參數 settings ' . $order->key->settings));
    }

    protected function setBaseRequest($order, $post):void {

        $this->logRequest($order, $post);
        # 設定model
        $this->setOrder($order);
        # 驗證輸入
        $this->validateOrderInput($post);
        # decode
        $settings = $this->getSettings($order);
        # 建立sign
        $this->setCreateSign($post, $settings);

        # 設定商戶domain
        $this->setDoamin($settings);
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
    protected function validationCreateInput() {
        return [];
    }
    # 設定發送sign
    protected function setCreateSign($post, $settings) {
        $this->createSign = '';
    }
    # 設定送單array
    abstract protected function setCreatePostData($post, $settings);
    # 設定header
    protected function getCurlHeader() {
        return [];
    }
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

    protected function getProxyIp() {
        if ($this->isHttps()) {
            return config('app.proxy_ip') . ':8443';
        }
        return  config('app.proxy_ip') . ':8080';
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
        if ($this->isCurlProxy) {
            return 'http://' . $this->getProxyIp() . $this->createSegments;;
        }

        $http = ($this->isHttps()) ? 'https://' : 'http://';
        return  $http . $this->domain. $this->createSegments;
    }

    public function setDoamin($settings) {
        if ($this->isDomainDynamic) {
            if (empty($settings['note1'])) {
                throw new WithdrawException(' empty dynamic domain in settings note1 ', Status::ORDER_FAILED);
            }
            $this->domain = $settings['note1'];
        }
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

        Log::channel('withdraw')->info(new LogLine('CURL url:' .$url.' result' . print_r($curlRes, true). ' header ' . print_r($this->getCurlHeader(), true)));
        Log::channel('withdraw')->info(new LogLine('CURL createPostData url:'.$url. ' '. print_r($this->getCreatePostData(), true)));

        return $this->getSendReturn($curlRes);
    }

    protected function getCreatePostData() {
        return $this->createPostData;
    }

    public function getSettings($order) {
        $key = $order->key;

        if (empty($key)) {
            throw new WithdrawException("key not found." , Status::ORDER_FAILED);
        }

        return $this->decode($key->settings, true);
    }

    # for decode
    protected function decode($data) {
        $decode =  json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DecodeException(json_last_error() . 'decode error @'. $data . '@', Status::ORDER_ERROR);
        }
        return $decode;
    }

    // for test
    public function setCurl() {
        $this->curl = new Curl();
    }

}
