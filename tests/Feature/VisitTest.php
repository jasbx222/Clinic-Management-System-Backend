<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_start_visit()
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($doctor)->postJson('/api/visits', [
            'appointment_id' => $appointment->id,
            'chief_complaint' => 'Headache',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('visits', [
            'appointment_id' => $appointment->id,
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'in_consultation',
        ]);
    }

    public function test_doctor_can_end_visit()
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $visit = Visit::factory()->create([
            'doctor_id' => $doctor->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($doctor)->postJson("/api/visits/{$visit->id}/end");

        $response->assertStatus(200);
        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'status' => 'completed',
        ]);
    }

    public function test_doctor_cannot_edit_another_doctor_visit()
    {
        $doctor1 = User::factory()->create(['role' => 'doctor']);
        $doctor2 = User::factory()->create(['role' => 'doctor']);

        $visit = Visit::factory()->create([
            'doctor_id' => $doctor1->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($doctor2)->putJson("/api/visits/{$visit->id}", [
            'diagnosis' => 'Flu',
        ]);

        $response->assertStatus(403);
    }
}
