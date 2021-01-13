<?php

namespace App\Repositories;

use App\Models\Merchant;

class MerchantRepository
{
    public function getKey(Merchant $merchant)
    {
        return config('app.sign_key');
    }

    public function getNotifyUrl(Merchant $merchant)
    {
        return config('app.java_domain');
    }
}
