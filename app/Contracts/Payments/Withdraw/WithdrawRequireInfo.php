<?php


namespace App\Contracts\Payments\Withdraw;

use App\Constants\Payments\WithdrawInfo as C;

class WithdrawRequireInfo
{
    protected $type;
    protected $column;
    protected $bankCard;
    protected $bank;

    public function __construct(string $type = null, array $column = null, array $bankCard = null, array $bank = null)
    {
        $this->type = $type;
        $this->column = $column;
        $this->bankCard = $bankCard;
        $this->bank = $bank;
    }

    public function toArray()
    {
        $result['column'] = $this->column;
        if (in_array(C::BANK_CARD, $result['column'])) {
            $result['select'] = [C::BANK_CARD => $this->bankCard];
        }

        if (in_array(C::BANK, $result['column'])) {
            $result['select'] = [C::BANK => $this->bank];
        }

        return $result;
    }
}
