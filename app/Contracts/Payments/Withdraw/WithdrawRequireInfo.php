<?php


namespace App\Contracts\Payments\Withdraw;

use App\Constants\Payments\Type;
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
        $type = $this->type;
        $typeArray = Type::typeName;
        sort($this->column);
        $result['column'] = $this->column;
        if($type == $typeArray[2]){  # 銀行卡 下拉選單
            $result['select'] = [
                C::BANK_CARD => $this->bankCard,
                C::BANK      => $this->bank,
            ];
        }

        return $result;
    }


}
