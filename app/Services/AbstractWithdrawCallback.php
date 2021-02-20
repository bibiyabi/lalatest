<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\WithdrawOrder;
use App\Exceptions\WithdrawException;
use App\Constants\Payments\ResponseCode;
use App\Contracts\Payments\CallbackResult;
use App\Exceptions\InputException;
use App\Constants\Payments\Status;

abstract class  AbstractWithdrawCallback
{

    // 停止callback回應的訊息
    protected $callbackSuccessReturnString = '';
    // 回調的orderId位置
    protected $callbackOrderIdPosition = '';
    // 回調的狀態位置
    protected $callbackOrderStatusPosition = '';
    protected $callbackOrderAmountPosition = '';
    protected $callbackOrderMessagePosition = '';
    // 回調成功狀態
    protected $callbackSuccessStatus = [];
    // 回調確認失敗狀態
    protected $callbackFailedStatus = [];


    # callback 驗證變數
    protected function getCallbackValidateColumns() {
        return [];
    }

    public function callback(Request $request) {
        # 這個取價格小數點才不會有差 10.0000 依然是10.0000 request->post會消掉0
        $postJson = $this->getCallBackInput($request);
        $post = $request->post();
        $postSign  = $this->getCallbackSign($request);
        $this->validateCallbackInput($post);
        $this->getCallbackOrderId($post);
        $order = $this->getOrderFromCallback($post);
        $settings = $this->getSettings($order);
        $checkSign = $this->genCallbackSign($postJson, $settings);
        return $this->returnCallbackResult($post, $checkSign, $postSign, $order);
    }

    # 檢查回調input
    protected function validateCallbackInput($post) {
        $validator = Validator::make($post, $this->getCallbackValidateColumns());
        if ($validator->fails()) {
            throw new InputException('callback input check error'. json_encode($validator->errors()), ResponseCode::EXCEPTION);
        }
    }


    protected function getOrderFromCallback($post) {
        $order = WithdrawOrder::where('order_id', $this->getCallbackOrderId($post))->first();

        if (empty($order)) {
            throw new WithdrawException("Order not found." , ResponseCode::EXCEPTION);
        }
        return $order;

    }

    protected function getCallbackOrderId($post) {
        $orderId =  data_get($post, $this->callbackOrderIdPosition);

        if (empty($orderId)) {
            throw new WithdrawException("OrderId not found." , ResponseCode::EXCEPTION);
        }
        return $orderId;
    }

    protected function returnCallbackResult($callbackPost, $checkSign, $callBackSign, $order) {
        if (config('app.is_check_sign') && $checkSign !== $callBackSign) {
            throw new WithdrawException("check sign error checkSign " . $checkSign . ' callbackSign ' . $callBackSign  , ResponseCode::EXCEPTION);
        }

        # callback 訂單成功
        if (in_array($this->getCallbackOrderStatus($callbackPost), $this->callbackSuccessStatus)) {
            return new CallbackResult(
                true,
                $this->callbackSuccessReturnString,
                $order,
                $this->getCallbackAmount($order, $callbackPost),
                $this->getCallbackMessage($callbackPost)
            );
        }

        # callback 訂單失敗
        if (in_array($this->getCallbackOrderStatus($callbackPost), $this->callbackFailedStatus)) {
            return new CallbackResult(
                false,
                $this->callbackSuccessReturnString,
                $order,
                0,
                $this->getCallbackMessage($callbackPost)
            );
        }

        throw new WithdrawException("callback result error" . json_encode($callbackPost) , ResponseCode::EXCEPTION);
    }

    protected function getCallbackOrderStatus($post) {
        return data_get($post, $this->callbackOrderStatusPosition);
    }

    protected function genCallbackSign($postJson, $settings) {
        return $this->genSign($postJson, $settings);
    }

    protected function getCallbackAmount($order, $callbackPost) {
        if (empty($this->callbackOrderAmountPosition)) {
            return $order->amount;
        }

        return data_get($callbackPost, $this->callbackOrderAmountPosition);
    }

    protected function getCallbackMessage($callbackPost) {
        return data_get($callbackPost, $this->callbackOrderMessagePosition);
    }
}
