<?php

namespace Database\Factories;

use App\Models\WithdrawOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WithdrawOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_id' => 'D' . date('ymdhis') . $this->faker->numberBetween(1000, 9999),
            'user_id' => $this->faker->numberBetween(1, 99),
            'key_id' => $this->faker->numberBetween(1, 99),
            'amount' => $this->faker->numberBetween(1, 99),
            'gateway_id' => $this->faker->numberBetween(1, 99),
            'status' => $this->faker->randomElement([0, 10, 11, 12, 20, 21]),
            'order_param' => '{}',
            'created_at' => $this->faker->dateTimeThisMonth(),
            'updated_at' => now(),
            'no_notify' => $this->faker->boolean,
        ];
    }
}
