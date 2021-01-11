<?php
namespace App\Validations;

use Illuminate\Support\Facades\Validator;

class ApplyPayValidation
{

    static public function inputValidator($data)
    {



        $messages = $v->messages();
        return $v;

    }
}
