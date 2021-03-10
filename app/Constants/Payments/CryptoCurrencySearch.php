<?php

namespace App\Constants\Payments;

use App\Services\Payments\Withdraw\CryptCallbackResult;

interface CryptoCurrencySearch
{
    public function search($order):CryptCallbackResult;
    public function getCrypSearchResult($url);
}
