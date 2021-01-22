<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 100),
            'gateway_id' => $this->faker->numberBetween(1, 100),
            'user_pk' => $this->faker->numberBetween(1, 100),
            'settings' => '{"public_key":"brianHalfBank","info_title":"brianHalfBank","return_url":"http://商戶後台/recharge/notify","private_key":"brianHalfBank","notify_url":"brianHalfBank","merchant_number":"brianHalfBank","md5_key":"請填上md5密鑰","account":"brianHalfBank"}',
            'created_at' => $this->faker->dateTime,
            'updated_at' => now(),
        ];
    }
}
