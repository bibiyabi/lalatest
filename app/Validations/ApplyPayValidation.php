<?php
namespace App\Validations;

use Illuminate\Support\Facades\Validator;

class ApplyPayValidation
{

    static public function inputValidator($data)
    {


        $validator = Validator::make(['title' => '1'], [
          #  'OrderId' => 'alpha_num|max:20',
          'title' => 'required|max:255',
        ]);


        return $validator;

    }
}
