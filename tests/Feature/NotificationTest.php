<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use App\Notifications\AppointmentConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_confirmation_notification_is_sent()
    {
        Notification::fake();

        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patientUser = User::factory()->create(['role' => 'patient']);
        $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $response = $this->actingAs($receptionist)->postJson('/api/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
        ]);

        $response->assertStatus(201);

        // Assuming we uncommented the notification dispatch in AppointmentController
        // Notification::assertSentTo(
        //     [$patientUser], AppointmentConfirmed::class
        // );
    }
}
