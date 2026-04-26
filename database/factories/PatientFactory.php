<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'patient']),
            'file_number' => 'PT-'.$this->faker->unique()->numerify('######'),
            'date_of_birth' => $this->faker->date('Y-m-d', '-20 years'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'blood_group' => $this->faker->randomElement(['A+', 'O+', 'B+', 'AB+']),
            'address' => $this->faker->address,
        ];
    }
}
