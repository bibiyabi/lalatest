<?php

namespace App\Observers;

use App\Models\Merchant;
use Illuminate\Support\Facades\Cache;

class MerchantObserver
{
    public function retrieved(Merchant $merchant)
    {
        Cache::add("user.name.{$merchant->name}", $merchant, 60);
    }

    public function saved(Merchant $merchant)
    {
        Cache::add("user.name.{$merchant->name}", $merchant, 60);
    }

    public function deleted(Merchant $merchant)
    {
        Cache::forget("user.name.{$merchant->name}");
    }
}
