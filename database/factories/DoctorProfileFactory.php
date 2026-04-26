<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'doctor']),
            'specialization' => $this->faker->randomElement(['Cardiology', 'Dermatology', 'Neurology']),
            'consultation_fee' => $this->faker->randomFloat(2, 100, 500),
            'appointment_duration' => 30,
            'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ];
    }
}
