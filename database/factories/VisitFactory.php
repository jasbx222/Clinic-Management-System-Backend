<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory()->state(['role' => 'doctor']),
            'start_time' => now(),
            'chief_complaint' => $this->faker->sentence,
            'diagnosis' => $this->faker->sentence,
            'treatment_plan' => $this->faker->paragraph,
            'status' => 'in_progress',
        ];
    }
}
