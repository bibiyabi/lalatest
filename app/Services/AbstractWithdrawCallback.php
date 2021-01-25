<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\WithdrawOrder;
use App\Exceptions\WithdrawException;
use App\Constants\Payments\ResponseCode;
use App\Contracts\Payments\CallbackResult;

abstract class  AbstractWithdrawCallback
{
    # callback 驗證變數
    abstract protected function getCallbackValidateColumns();

    public function callback(Request $request) {
        # 這個取價格小數點才不會有差 10.0000 依然是10.0000 request->post會消掉0
        $postJson = $this->getCallBackInput();
        $post = $request->post();
        $postSign  = $this->getCallbackSign($request);
        $this->validateCallbackInput($post);
        $this->getCallbackOrderId($post);
        $order = $this->getOrderFromCallback($post);
        $settings = $this->getSettings($order);
        $checkSign = $this->genSign($postJson, $settings);
        return $this->returnCallbackResult($post, $checkSign, $postSign, $order);
    }

    # 檢查回調input
    protected function validateCallbackInput($post) {
        $validator = Validator::make($post, $this->getCallbackValidateColumns());
        if($validator->fails()){
            throw new WithdrawException('callback input check error'. json_encode($validator->errors()), ResponseCode::EXCEPTION);
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

        if ($checkSign !== $callBackSign) {
            throw new WithdrawException("check sign error" , ResponseCode::EXCEPTION);
        }

        # callback 訂單成功
        if (in_array($this->getCallbackOrderStatus($callbackPost), $this->callbackSuccessStatus)) {
            return new CallbackResult(true, $this->callbackSuccessReturnString, $order);
        }
        # callback 訂單失敗
        if (in_array($this->getCallbackOrderStatus($callbackPost), $this->callbackFailedStatus)) {
            return new CallbackResult(false, $this->callbackSuccessReturnString, $order);
        }


    }
}
