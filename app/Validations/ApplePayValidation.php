<?php
namespace App\Validations;

use Illuminate\Support\Facades\Validator;

class ApplyPayValidation
{

    static public function inputValidator($data)
    {
        /**
         * items[] = [
         *      'name' => 'abc',
         *      'qty' => 2,
         *      'unit' => 'piece',
         *      'price' => 50
         * ];
         */
        $validator = Validator::make($data, [
            'OrderId' => 'alpha_num|max:20',

        ]);

        return $validator;
    }
}
