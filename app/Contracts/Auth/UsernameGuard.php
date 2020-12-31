<?php

namespace App\Contracts\Auth;

use Illuminate\Auth\TokenGuard;

class UsernameGuard extends TokenGuard
{
    public function getTokenForRequest()
    {
        return $this->request->header($this->inputKey);
    }
}
