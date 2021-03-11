<?php

namespace Tests\Unit;

use App\Lib\Hash\Signature;
use Tests\TestCase;

class SignTest extends TestCase
{
    /**
     * @dataProvider get_data
     */
    public function test_sign($data, $userSign)
    {
        $this->assertEquals($userSign, Signature::makeSign($data, 'b6687fdce21aabf3d2493c8350d4275f'));
    }

    public function get_data()
    {
        return [
            [
                [
                    'gateway_name'  => 'Inrusdt',
                    'is_deposit'    => '1',
                    'type'          => 'bank_card',
                ],
                'D31BE56203D1D5865C2EAD629EBC2B72'
            ],
            [
                [
                    'tx_id'     => '22',
                    'order_id'  => 'D210220115119681115952',
                    'pk'        => '32',
                    'amount'    => '2',
                    'type'      => 'bank_card',
                ],
                '607ED1199362DCEDDC8F9CFFA61C0581'
            ],
        ];
    }
}
