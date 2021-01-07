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

    public function getBanks() {
        return [
            '中国工商银行' => 	'1',
            '中国农业银行' => 	'2',
            '中国银行' => 	'3',
            '中国建设银行' => 	'4',
            '交通银行' => 	'5',
            '中国邮政储蓄' => 	'6',
            '中信银行' => 	'7',
            '中国民生银行' => 	'8',
            '中国光大银行' => 	'9',
            '招商银行' => 	'10',
            '上海浦东发展银行' => 	'11',
            '广东发展银行' => 	'12',
            '上海银行' => 	'13',
            '北京银行' => 	'14',
            '兴业银行' => 	'15',
            '华夏银行' => 	'16',
            '上海农村商业银行' => 	'17',
            '北京农村商业银行' => 	'18',

            '平安银行' => 	'20',
            '宁波银行' => 	'21',
            '渤海银行' => 	'22',
            '浙商银行' => 	'23',
            '南京银行' => 	'24',
            '杭州银行' => 	'25',
            '东亚银行' => 	'26',

            '成都银行' => 	'31',

            '厦门银行' => 	'44',

            '河北银行' => 	'81',

            '广州农商银行' => 	'87',

            '浙江泰隆商业银行' => '94',
            '桂林银行' => '95',


        ];
    }


}
