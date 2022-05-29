<?php

namespace Database\Factories;

use App\Models\Birthdate;
use Illuminate\Database\Eloquent\Factories\Factory;

class BirthdateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Birthdate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'birthdate' => $this->faker->dateTimeThisCentury->format('Y-m-d'),
            'timezone' => $this->faker->timezone()
        ];
    }
}
