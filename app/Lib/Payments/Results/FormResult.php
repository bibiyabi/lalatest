<?php

namespace App\Lib\Payments\Results;

use App\Contracts\Payments\HttpParam;
use App\Contracts\Payments\Results\ResultFactoryInterface;

class FormResult implements ResultFactoryInterface
{
    public function getResult(HttpParam $param): Result
    {
        $bodies = $param->getBody();
        $rs = '<form action="'.$param->getUrl().'" method="'.$param->getMethod().'">';
        foreach ($bodies as $name => $value) {
            $rs .= $this->genInputColumn($name, $value);
        }
        $rs .= '</form>';

        return new Result('form', $rs);
    }

    protected function genInputColumn($name, $value)
    {
        return '<input name="'.$name.'" value="'.$value.'" hidden="true">';
    }
}
