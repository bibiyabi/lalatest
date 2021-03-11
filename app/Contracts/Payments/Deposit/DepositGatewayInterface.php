<?php

namespace App\Contracts\Payments\Deposit;

use App\Services\Payments\Deposit\DepositRequireInfo;
use App\Contracts\Payments\Placeholder;
use App\Models\Order;
use App\Contracts\Payments\HttpParam;
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
    public function depositCallback(Request $order): CallbackResult;

    /**
     * 提示字
     * @param $type
     * @return Placeholder
     */
    public function getPlaceholder($type): Placeholder;

    public function getRequireInfo($type): DepositRequireInfo;
}
