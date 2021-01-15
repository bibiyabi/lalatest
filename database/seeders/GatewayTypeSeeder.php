<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GatewayTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('gateway_types')->insert([
            'gateways_id'           => 21,
            'types_id'              => 4,
            'is_support_deposit'    => 1,
            'is_support_withdraw'   => 1
        ]);
    }
}
