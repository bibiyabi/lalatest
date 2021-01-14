<?php

namespace App\Services;

class Signature
{
    public static function makeSign($data, $key)
    {
        ksort($data);
        return md5(http_build_query($data) . $key);
    }
}
