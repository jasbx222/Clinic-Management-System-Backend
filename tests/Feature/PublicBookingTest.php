<?php

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\AppointmentConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup generic user & doctor
    $this->doctorUser = User::factory()->create(['role' => 'doctor']);
    $this->doctorProfile = DoctorProfile::factory()->create([
        'user_id' => $this->doctorUser->id,
        'consultation_fee' => 200,
        'appointment_duration' => 30,
    ]);
});

test('public user can fetch clinic public information', function () {
    // Mocking an endpoint that might exist for clinic info
    $response = $this->getJson('/api/public/clinic');
    // Assuming this returns 200 with clinic name etc.
    // If not implemented yet, just checking the concept
    $response->assertStatus(200);
})->skip('Endpoint not implemented yet');

test('public user can book an appointment and patient record is created', function () {
    Notification::fake();

    $bookingData = [
        'doctor_id' => $this->doctorUser->id,
        'appointment_date' => now()->addDays(2)->format('Y-m-d'),
        'appointment_time' => '10:00:00',
        'patient_name' => 'John Doe',
        'patient_phone' => '0501234567',
        'reason' => 'Routine checkup',
    ];

    // Assuming a public endpoint for booking exists
    $response = $this->postJson('/api/public/appointments', $bookingData);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'status', 'appointment_date']]);

    // Assert patient created
    $this->assertDatabaseHas('users', ['phone' => '0501234567', 'name' => 'John Doe']);
    $patientUser = User::where('phone', '0501234567')->first();
    $this->assertDatabaseHas('patients', ['user_id' => $patientUser->id]);

    // Assert appointment created
    $this->assertDatabaseHas('appointments', [
        'doctor_id' => $this->doctorUser->id,
        'appointment_date' => $bookingData['appointment_date'],
        'status' => 'confirmed',
    ]);

    // Assert Notification Sent
    Notification::assertSentTo(
        [$patientUser], AppointmentConfirmed::class
    );
})->skip('Endpoint not implemented yet');

test('public user cannot book an already reserved time slot (Race condition)', function () {
    $date = now()->addDays(2)->format('Y-m-d');

    // Create existing appointment
    $patient = Patient::factory()->create();
    Appointment::factory()->create([
        'doctor_id' => $this->doctorUser->id,
        'patient_id' => $patient->id,
        'appointment_date' => $date,
        'appointment_time' => '10:00:00',
        'status' => 'confirmed',
    ]);

    $bookingData = [
        'doctor_id' => $this->doctorUser->id,
        'appointment_date' => $date,
        'appointment_time' => '10:00:00', // Same time
        'patient_name' => 'Jane Doe',
        'patient_phone' => '0509876543',
    ];

    $response = $this->postJson('/api/public/appointments', $bookingData);

    // Should return validation error for conflict
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['appointment_time']);
})->skip('Endpoint not implemented yet');
