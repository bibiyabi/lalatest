<?php

namespace App\Lib\Payments\Results;

use App\Contracts\Payments\Results\ResultFactoryInterface;

class ResultFactory
{
    public static function createResultFactory($type): ResultFactoryInterface
    {
        switch ($type) {
            case 'url':
                $factory = new UrlResult();
                break;

            case 'form':
                $factory = new FormResult();
                break;

            default:
                throw new \Exception("Result factory not found", 1);
                break;
        }

        return $factory;
    }
}
