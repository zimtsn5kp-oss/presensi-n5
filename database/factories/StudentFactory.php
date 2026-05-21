<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nisn' => fake()->unique()->numerify('00########'),
            'name' => fake()->name(),
            'classroom' => fake()->randomElement(['VII A', 'VII B', 'VIII A', 'IX A']),
            'gender' => fake()->randomElement(['L', 'P']),
            'address' => fake()->address(),
            'fingerprint_id' => fake()->unique()->numberBetween(3000, 9000),
            'is_active' => true,
        ];
    }
}
