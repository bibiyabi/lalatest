<?php
namespace App\Constants\Payments;


class Type
{
    const type = [
        'credit_card'       => 1,
        'bank_card'         => 2,
        'e_wallet'          => 3,
        'cryptocurrency'    => 4,
        ];

    const typeName = [
        1 => 'credit_card',
        2 => 'bank_card',
        3 => 'e_wallet',
        4 => 'cryptocurrency',
    ];

}
