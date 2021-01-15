<?php

namespace App\Services;

class Signature
{
    public static function makeSign($data, $secret)
    {
        ksort($data);
        $string = '';
        foreach ($data as $key => $value) {
            $string .= '&' . $key . '=' . $value;
        }
        $string .= $secret;
        $string = substr($string, 1);

        return strtoupper(md5($string));
    }
}
