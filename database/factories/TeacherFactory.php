<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nip' => fake()->unique()->numerify('##################'),
            'name' => fake()->name(),
            'subject' => fake()->randomElement(['Aqidah Akhlak', 'Fiqih', 'Quran Hadis', 'Bahasa Arab']),
            'position' => fake()->randomElement(['Guru Mapel', 'Wali Kelas', 'Kepala Madrasah']),
            'fingerprint_id' => fake()->unique()->numberBetween(9100, 9900),
            'is_active' => true,
        ];
    }
}
