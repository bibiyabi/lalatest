<?php
namespace App\Constants\Payments;


class Type
{
    public const CREDIT_CARD        = 'credit_card';
    public const BANK_CARD          = 'bank_card';
    public const WALLET             = 'e_wallet';
    public const CRYPTO_CURRENCY    = 'cryptocurrency';

    const type = [
        'bank_card'         => 1,
        'e_wallet'          => 2,
        'cryptocurrency'    => 3,
        'credit_card'       => 4,
    ];

    const typeName = [
        1 => self::BANK_CARD,
        2 => self::WALLET,
        3 => self::CRYPTO_CURRENCY,
        4 => self::CREDIT_CARD,
    ];

}
