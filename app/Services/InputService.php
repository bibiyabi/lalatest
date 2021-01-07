<?php

namespace App\Services;
use App\Exceptions\InputException;

class InputService
{

    public function checkIsset($post, $array = [])
    {
        foreach ($array as $key) {
            if (!isset($post[$key])) {
                throw new InputException('param ' . $key . ' not found', '005');
            }
        }
    }
}
