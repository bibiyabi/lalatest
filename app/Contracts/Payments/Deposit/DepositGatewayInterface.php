<?php

namespace App\Contracts\Payments\Deposit;

use App\Contracts\Payments\Placeholder;
use App\Models\Order;
use App\Contracts\Payments\HttpParam;
use App\Contracts\Payments\OrderResult;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use App\Contracts\Payments\CallbackResult;

interface DepositGatewayInterface
{
    /**
     * 取得下單參數
     *
     * @param Order $order
     * @return HttpParam
     */
    public function genDepositParam(Order $order) : HttpParam;

    /**
     * 處裡下單結果
     *
     * @param Response|form $rs 可能是打第三方的 Response 或 自己產的 HTML Form
     * @return string 可能是跳轉 Url 或 HTML Form
     */
    public function processOrderResult($rs): string;

    /**
     * 取得下單方式 post get patch ...
     *
     * @return string
     */
    public function getDepositHttpMethod(): string;

    /**
     * 取得返回格式 form url
     *
     * @return string
     */
    public function getReturnType(): string;

    /**
     * 處裡下單回調
     *
     * @param Request $order
     * @return CallbackResult
     */
    public function depositCallback(Request $order) : CallbackResult;

    /**
     * 提示字
     * @param $type
     * @return Placeholder
     */
    public function getPlaceholder($type):Placeholder;
}
