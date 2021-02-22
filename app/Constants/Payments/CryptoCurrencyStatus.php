<?php

namespace App\Constants\Payments;

interface CryptoCurrencyStatus
{
    public const ORDER_SUCCESS = 1;

    public const ORDER_FAIL = 2;

    public const API_FAIL = 3;

    public const ORDER_NOT_FOUND = 4;

}
