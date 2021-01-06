<?php

namespace App\Contracts\Payments\Results;

use App\Contracts\Payments\OrderParam;

class FormResult implements ResultFactory
{
    public function getResult(OrderParam $param)
    {
        $bodies = $param->getBody();
        $rs = '<form action="'.$param->getUrl().'" method="'.$param->getMethod().'">';
        foreach ($bodies as $name => $value) {
            $rs .= $this->genInputColumn($name, $value);
        }
        $rs .= '</form>';

        return $rs;
    }

    protected function genInputColumn($name, $value)
    {
        return '<input name="'.$name.'" value="'.$value.'" hidden="true">';
    }
}
