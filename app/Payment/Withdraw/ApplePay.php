<?php
namespace App\Payment\Withdraw;
use App\Services\AbstractWithdrawGateway;
use App\Validations\ApplyPayValidation;
use App\Exceptions\WithdrawException;
use App\Collections\ApplePayCollection;
use App\Collections\JavaBanksCollection;
use App\Services\InputService;
use app\Constants\WithDrawOrderStatus;

class ApplePay extends AbstractWithdrawGateway
{

    public function __construct( ) {
        echo __CLASS__.__FUNCTION__;
    }

    public function setRequest($order, $vendor, InputService $inputService, ApplePayCollection $applyPayCollection,
     JavaBanksCollection $JavaBanksCollection) {

        $inputService->checkIsset($order, ['amount', 'order_id', 'card_no',
             'bank_code']);

        $params = [];
        $params['version']    = 'V1.0';
        $params['appId']      = $vendor['business_number'];  //商戶號
        $params['orderType']    = '1';
        $params['merchOrderNo'] = $order['order_id'];

        $params['orderDate']      = date('YmdHis');
        $params['amount'] = $order['price'];
        $params['notifyUrl'] = $vendor['server_back_url'];
        $params['clientIp'] = '10.21.211.111';
        $params['accNo'] = $order['card_no'];
        $params['accName'] = $order['name'];

        $bankList = $applyPayCollection->getBanksList();



        $bankName = $JavaBanksCollection->getBankNameByJavaBankCode($order['bank_code']);

        if ($bankName === false) {
            return ['status' => false, 'msg' => ' bank_name not found ' . $e->getMessage() . json_encode($order) . json_encode($vendor) ,  'code' => WithDrawOrderStatus::FAIL];
        }

        if (!isset($bankList[$bankName])) {
            return ['status' => false, 'msg' => 'bank not found ' . $bankName ,  'code' => WithDrawOrderStatus::FAIL];
        }

        return $this;
    }

    public function send() {

    }

    public function getOrderRes() {
        echo 'apple getOrderRes';
    }

    public function getRedirectType() {
        return 'curl';
    }
}
