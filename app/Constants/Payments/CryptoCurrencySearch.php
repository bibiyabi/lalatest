<?php

namespace App\Constants\Payments;

use App\Payment\CryptCallbackResult;

interface CryptoCurrencySearch
{
    public function search($order):CryptCallbackResult;
    public function getCrypSearchResult($url);
}
