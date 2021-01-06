<?php
namespace App\Collections;

use App\Exceptions\WithdrawException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ApplePayCollection extends Collection
{
    public $merchantId;
    public $attributes;
    public $notifyUrl;
    public $returnUrl;

    public function __construct()
    {
        parent::__construct();

    }

    public function setData($data)
    {
        $this->attributes = $data;
        return $this;
    }

     /**
     * @return $this

     */
    public function setPostData()
    {

        $this->put('MerchantID', 'aaa');

        return $this;
    }

    /**
     * @return $this

     */
    public function setSignKey()
    {
        if (empty($this->attributes)) {
            throw new WithdrawException('attributes must be set');
        }

        $this->put('sign' ,1);
        return $this;
    }


}
