<?php
namespace App\Services\Payments\WithdrawGateways;

use App\Constants\Payments\Type;
use App\Contracts\Payments\Placeholder;
use App\Contracts\Payments\Withdraw\WithdrawRequireInfo;
use App\Exceptions\WithdrawException;
use App\Services\AbstractWithdrawGateway;
use App\Payment\Curl;
use Illuminate\Http\Request;
use App\Constants\Payments\WithdrawInfo as C;
use App\Contracts\LogLine;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Log;
use App\Constants\Payments\ResponseCode;
use App\Exceptions\InputException;
use Illuminate\Support\Facades\Http;
use App\Payment\CryptCallbackResult;
use App\Constants\Payments\CryptoCurrencyStatus;


class Binance extends AbstractWithdrawGateway
{
    // ================ 下單參數 ==================
    // 下單domain
    protected $domain = 'api.binance.com';
    // 下單網址
    protected $createSegments = '/wapi/v3/withdraw.html';
    // 設定下單sign
    protected $createSign;
    protected $isCurlProxy = true;

    public function __construct(Curl $curl) {

        parent::__construct($curl);

    }

    public function setRequest($post = [], WithdrawOrder $order) : void {

        $this->setBaseRequest($order, $post);
    }

    protected function validationCreateInput() {

        return [
            'order_id'         => 'required',
            'withdraw_address' => 'required',
            'amount'           => 'required',
        ];
    }

    protected function setCreateSign($post, $settings) {

        $signParams = $this->getNeedGenSignArray($post,  $settings);
        $this->createSign =  $this->genSign($signParams, $settings);
    }

    private function getNeedGenSignArray($input, $settings) {
        $this->setCallBackUrl(__CLASS__);

        $array = [
            "asset"           => $settings['coin'],
            "network"         => $settings['blockchain_contract'],
            "withdrawOrderId" => $input['order_id'],
            "address"         => $input['withdraw_address'],
            "amount"          => $input['amount'],
            "timestamp"       => time() . '000',
        ];

        var_dump($array);

        return $array;

    }

    protected function genSign($postData, $settings) {
        return hash_hmac('sha256', http_build_query($postData, '', '&'), $settings['private_key']);
    }

    protected function setCreatePostData($post, $settings) {
        $this->api_key = $settings['md5_key'];

        $options = $this->getNeedGenSignArray($post, $settings);
        $options['signature'] = $this->createSign;

        $this->createPostData =  http_build_query($options, '', '&');;
    }

    public function isHttps() {
        return true;
    }

    protected function getCurlHeader() {
        return [
            "HOST: ". $this->domain,
            'X-MBX-APIKEY: ' . $this->api_key,
        ];
    }

    protected function checkCreateOrderIsSuccess($res) {
        return isset($res['success']) && $res['success'] === true;
    }

    // ======================= 下拉提示 ===========================

    public function getPlaceholder($type):Placeholder
    {
        switch ($type) {
            case Type::CRYPTO_CURRENCY:
                $coin = ['TRC20'];
                $blockchainContract = ['USDT'];
                break;
            default:
                $coin = [];
                $blockchainContract = [];
                break;
        }

        return new Placeholder(
            $type,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            [],
            $coin,
            $blockchainContract
        );
    }

    public function getRequireInfo($type): WithdrawRequireInfo
    {
        # 該支付有支援的渠道  指定前台欄位
        $column = [];
        if ($type == Type::BANK_CARD) {
            $column = [
                C::AMOUNT,
                C::IFSC,
                C::BANK_ACCOUNT,
                C::BANK_ADDRESS,
                C::FIRST_NAME,
                C::LAST_NAME,
                C::MOBILE,
                C::EMAIL,
                C::FUND_PASSWORD
            ];
        }

        return new WithdrawRequireInfo($type, $column, [], []);
    }


    public function search($order):CryptCallbackResult
    {
        $settings = $this->getSettings($order);
        $this->api_key = $settings['md5_key'];
        $this->api_secret = $settings['private_key'];

        $params = [
            'asset' => $settings['coin'],
            'startTime' => (time() - 60 * 60 * 24) * 1000,   // 24 hour ago
            'endTime' => time() * 1000,
            'timestamp' => time() * 1000,
        ];

        $query = http_build_query($params, '', '&');
        $signature = hash_hmac('sha256', $query, $this->api_secret);
        $url = 'http://'.$this->getProxyIp().'/wapi/v3/withdrawHistory.html' .'?' . $query . '&signature=' . $signature;

        $res = $this->curl
        ->setUrl($url)
        ->setHeader($this->getCurlHeader())
        ->followLocation()
        ->exec();

        $res = $this->decode($res['data']);

        if (!isset($res['success']) || !isset($res['withdrawList']) || $res['success'] !== true ) {
            return new CryptCallbackResult(CryptoCurrencyStatus::API_FAIL, json_encode($res));
        }

        if (empty($res['withdrawList'])) {
            return new CryptCallbackResult(CryptoCurrencyStatus::ORDER_NOT_FOUND, json_encode($res));
        }

        foreach ($res['withdrawList'] as $history) {

            if ($order['order_id'] === $history['withdrawOrderId']) {
                continue;
            }

            # 提現失敗
            if (in_array($history['status'], [1,3,5])) {
                return new CryptCallbackResult(CryptoCurrencyStatus::ORDER_FAIL, json_encode($res));
            }

            # 提現成功
            if ($history['status'] === 6) {
                $cryptResult =  new CryptCallbackResult(CryptoCurrencyStatus::ORDER_SUCCESS, json_encode($res));
                $cryptResult->setAmount($history['amount']);
                return $cryptResult;
            }
        }

        return new CryptCallbackResult(CryptoCurrencyStatus::ORDER_NOT_FOUND, json_encode($res));

    }
}
