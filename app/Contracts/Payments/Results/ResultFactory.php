<?php

namespace App\Contracts\Payments\Results;

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
