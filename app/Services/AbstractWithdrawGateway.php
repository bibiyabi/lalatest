<?php
namespace App\Services;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\WithdrawRequireInfo;
use Illuminate\Http\Request;
use App\Payment\Curl;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Contracts\Payments\LogLine;
use App\Services\AbstractWithdrawCallback;
use App\Services\Payments\ResultTrait;
use App\Payment\Proxy;
use App\Exceptions\WithdrawException;
use App\Constants\Payments\ResponseCode;
abstract class AbstractWithdrawGateway extends AbstractWithdrawCallback
{
    use ResultTrait;
    use Proxy;
    # url object
    protected $curl;
    # 回調網址
    protected $callbackUrl;
    # order modal
    protected $order;
    # 建單sign
    protected $createSign;
    protected $createPostData = [];


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
    # 設定發送sign
    abstract protected function setCreateSign($post, $settings);
    # 設定送單array
    abstract protected function setCreatePostData($post, $settings);

    abstract protected function getCurlHeader();
    abstract protected function isCurlUseSSL();
    abstract protected function getCreateUrl();
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
            throw new WithdrawException($validator->errors(), ResponseCode::ERROR_PARAMETERS);
        }
    }
    protected function getCreateOrderRes($curlRes) {
        return $this->decode($curlRes['data']);
    }

    # 取得建單狀態
    protected function getSendReturn($curlRes) {
        #dd($curlRes);

        switch ($curlRes['code']) {
            case Curl::STATUS_SUCCESS:
                $createRes = $this->getCreateOrderRes($curlRes['data']);
                return $this->returnCreateRes($createRes);
            case Curl::FAILED:
                return $this->resCreateFailed('', ['order_id' => $this->order->order_id]);
            case Curl::TIMEOUT:
                return $this->resCreateRetry('', ['order_id' => $this->order->order_id]);
            default:
                throw new WithdrawException("curl rescode default " , ResponseCode::EXCEPTION);
        }
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
            throw new WithdrawException('setting empty ', ResponseCode::ERROR_PARAMETERS);
        }
        $this->order = $order;
    }

    protected function getCreateSign() {
        return $this->createSign;
    }


    public function send() {

        if (empty($this->getCreatePostData())) {
            throw new WithdrawException('createPostData empty ', ResponseCode::ERROR_PARAMETERS);
        }

        $url = $this->getCreateUrl();

        if ($this->isCurlUseSSL()) {
            $this->curl->ssl();
        }

        $curlRes = $this->curl
        ->setUrl($url)
        ->setHeader($this->getCurlHeader())
        ->setPost($this->getCreatePostData())
        ->exec();

        Log::channel('withdraw')->info(new LogLine('CURL 回應'), [$curlRes, $this->createPostData]);

        return $this->getSendReturn($curlRes);
    }

    protected function getCreatePostData() {
        return $this->createPostData;
    }

    protected function getSettings($order) {
        $key = $order->key;

        if (empty($key)) {
            throw new WithdrawException("key not found." , ResponseCode::EXCEPTION);
        }

        return $this->decode($key->settings);
    }

    # for decode
    protected function decode($data) {
        return json_decode($data, true);
    }

}
