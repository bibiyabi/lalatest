<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Http\Request;
use App\Constants\WithdrawPayments;
use App\Http\Controllers\Controller;

class WithdrawPaymentsController extends Controller
{
    public function getSupportBankCards(Request $request) {
        var_dump(WithdrawPayments::getBankCards()->toArray());

    }

    public function getSupportWallet() {

    }

    public function getSupportDigitalCurrency() {

    }





}
