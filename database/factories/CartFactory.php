<?php

namespace Database\Factories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'count' => $this->faker->numberBetween(1,5),
            'product_id' => $this->faker->numberBetween(1,100),
            'user_id' => $this->faker->numberBetween(1,20),
            'status' => $this->faker->numberBetween(0,2),
            'size_id' => rand(1,20),
            'color_id' => rand(1,10),
        ];
    }
}
