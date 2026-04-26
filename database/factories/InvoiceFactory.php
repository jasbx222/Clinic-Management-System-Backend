<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 1000);
        $tax = $subtotal * 0.15;

        return [
            'patient_id' => Patient::factory(),
            'appointment_id' => Appointment::factory(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'total' => $subtotal + $tax,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ];
    }
}
