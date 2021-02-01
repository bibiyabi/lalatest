<?php

namespace App\Contracts\Auth;

use Cache;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;

class CacheUserProvider extends EloquentUserProvider implements UserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $username = $credentials['name'];

        // Cache controll by MerchantObserver
        return Cache::get("user.name.$username") ?? parent::retrieveByCredentials($credentials);
    }
}
