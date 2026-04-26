<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_appointment()
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient = Patient::factory()->create();

        $response = $this->actingAs($receptionist)->postJson('/api/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'status' => 'pending',
        ]);
    }

    public function test_prevent_double_booking_same_doctor_time()
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();
        $date = now()->addDay()->format('Y-m-d');

        // Book first appointment
        $this->actingAs($receptionist)->postJson('/api/appointments', [
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00',
        ]);

        // Attempt double book
        $response = $this->actingAs($receptionist)->postJson('/api/appointments', [
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appointment_time']);
    }

    public function test_mark_appointment_as_arrived_moves_to_waiting_list()
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $appointment = Appointment::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($receptionist)->postJson("/api/appointments/{$appointment->id}/arrive");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'waiting');
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'waiting',
        ]);
    }
}
