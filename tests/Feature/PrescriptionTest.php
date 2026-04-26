<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_create_prescription_with_multiple_medicines()
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient = Patient::factory()->create();
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($doctor)->postJson('/api/prescriptions', [
            'visit_id' => $visit->id,
            'items' => [
                [
                    'name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => 'Twice a day',
                    'duration' => '5 Days',
                ],
                [
                    'name' => 'Amoxicillin',
                    'dosage' => '250mg',
                    'frequency' => 'Three times a day',
                    'duration' => '7 Days',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('prescriptions', ['visit_id' => $visit->id]);
        $this->assertDatabaseCount('prescription_items', 2);
    }

    public function test_cannot_create_prescription_with_empty_medicines()
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $visit = Visit::factory()->create(['doctor_id' => $doctor->id]);

        $response = $this->actingAs($doctor)->postJson('/api/prescriptions', [
            'visit_id' => $visit->id,
            'items' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }
}
