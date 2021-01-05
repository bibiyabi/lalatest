<?php
namespace app\Constants;

class WithdrawPayments
{
    /**
     * 不指定。
     */
    const None = '';

    const BANKCARD_APPLEPAY = 'ApplyPay';
    const WALLET_APPLEPAY = 'ApplyPay';
    const DIGITAL_CURRENCYS_APPLEPAY = 'ApplyPay';

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getBankCards()
    {
        return collect([
            self::BANKCARD_APPLEPAY,
        ])->unique();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getWallets()
    {
        return collect([
            self::WALLET_APPLEPAY,
        ])->unique();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    static public function getDigitalCurrencys()
    {
        return collect([
            self::DIGITAL_CURRENCYS_APPLEPAY,
        ])->unique();
    }

}
