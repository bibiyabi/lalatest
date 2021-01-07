<?php

namespace App\Contracts\Payments\Deposit;

use App\Models\Order;
use App\Contracts\Payments\HttpParam;
use App\Contracts\Payments\OrderResult;

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
     * @param string $rs App
     * @return OrderResult
     */
    public function processOrderResult($rs): OrderResult;

    /**
     * 取得下單方式 post get patch ...
     *
     * @return string
     */
    public function getDepositHttpMethod(): string;

    /**
     * 取得返回格式 form url
     *
     * @return void
     */
    public function getReturnType(): string;

    /**
     * 處裡下單回調
     *
     * @param Order $order
     * @return OrderResult
     */
    public function depositCallback(Order $order) : OrderResult;
}
