<?php


namespace App\Common;


use App\Exceptions\CustomException;

class ExceptionHandler
{
    public static function exceptionThrow($error, $parameter = null)
    {
        throw new CustomException($error, $parameter);
    }
}
