<?php

namespace App\Contracts\Payments\Deposit;

use App\Constants\Payments\DepositInfo as C;

class DepositRequireInfo
{
    protected $type;
    protected $column;
    protected $bank;

    public function __construct(string $type = null, array $column = null, array $bank = null)
    {
        $this->type = $type;
        $this->column = $column;
        $this->bank = $bank;
    }

    public function toArray()
    {
        $result['column'] = $this->column;

        if (in_array(C::BANK, $result['column'])){
            $result['select'] = [C::BANK => $this->bank];
        }

        return $result;
    }
}
