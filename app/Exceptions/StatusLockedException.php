<?php

namespace App\Exceptions;

use Exception;

class StatusLockedException extends Exception
{
    // 訂單狀態不可變更
}
