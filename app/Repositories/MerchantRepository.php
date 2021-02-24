<?php

namespace App\Repositories;

class MerchantRepository
{
    public function getKey()
    {
        return config('app.sign_key');
    }

    public function getNotifyUrl()
    {
        return config('app.java_domain');
    }
}
