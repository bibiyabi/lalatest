<?php
namespace App\Constants\Payments;


class Type
{
    public const CREDIT_CARD        = 'credit_card';
    public const BANK_CARD          = 'bank_card';
    public const WALLET             = 'e_wallet';
    public const CRYPTO_CURRENCY    = 'cryptocurrency';

    const type = [
        'credit_card'       => 1,
        'bank_card'         => 2,
        'e_wallet'          => 3,
        'cryptocurrency'    => 4,
        ];

    const typeName = [
        1 => self::CREDIT_CARD,
        2 => self::BANK_CARD,
        3 => self::WALLET,
        4 => self::CRYPTO_CURRENCY,
    ];

}
