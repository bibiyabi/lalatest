<?php

namespace App\Contracts\Payments\Deposit;

class DepositRequireInfo
{
    protected $type;
    protected $column;

    public function __construct(string $type = null, array $column = null)
    {
        $this->type = $type;
        $this->column = $column;
    }

    public function toArray()
    {
        sort($this->column);
        $result['column'] = $this->column;

        return $result;
    }
}
