<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'user_id'   =>1,
            'gateway_id'=>1,
            'user_pk'   =>2,
            'settings'      =>'{"id":2,"type":2,"msgName":"linepaypaypay","bankName":"line","secondName":"bear","firstName":"big","cardNumber":987654321,"ifsc":1234567890,"cashflowMerchant":"linepay","cashflowUserId":"gggg","cashflowMerchantId":6666,"md5":"heh4hehe","publickey":null,"privatekey":null,"syncAddress":"http:\/\/google.com","asyncAddress":"http:\/\/google.com","channelId":987654,"remark1":null,"remark2":null}',

        ]);
    }
}
