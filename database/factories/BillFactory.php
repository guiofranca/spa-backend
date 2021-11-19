<?php

namespace Database\Factories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bill::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => $this->faker->numberBetween(1000, 30000),
            'description' => $this->faker->text(40),
            'category_id' => $this->faker->numberBetween(1,11),
            'paid_at' => now()->format('Y-m-d'),
        ];
    }
}
