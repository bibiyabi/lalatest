<?php

namespace App\Contracts\Payments;

interface Status
{
    public const PENDING = 0;

    public const ORDER_SUCCESS = 10;

    public const ORDER_FAILED = 11;

    public const ORDER_ERROR = 12;

    public const CALLBACK_SUCCESS = 20;

    public const CALLBACK_FAILED = 21;
}
