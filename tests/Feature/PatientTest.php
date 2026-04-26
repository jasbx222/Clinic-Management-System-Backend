<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_patients()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Patient::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/patients');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_patient_can_only_view_own_profile()
    {
        $patientUser = User::factory()->create(['role' => 'patient']);
        $ownPatient = Patient::factory()->create(['user_id' => $patientUser->id]);

        $otherPatientUser = User::factory()->create(['role' => 'patient']);
        $otherPatient = Patient::factory()->create(['user_id' => $otherPatientUser->id]);

        $responseOwn = $this->actingAs($patientUser)->getJson('/api/patients/'.$ownPatient->id);
        $responseOwn->assertStatus(200);

        $responseOther = $this->actingAs($patientUser)->getJson('/api/patients/'.$otherPatient->id);
        $responseOther->assertStatus(403);
    }

    public function test_receptionist_can_create_patient()
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);
        $newUser = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($receptionist)->postJson('/api/patients', [
            'user_id' => $newUser->id,
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('patients', [
            'user_id' => $newUser->id,
            'gender' => 'male',
        ]);
    }

    public function test_doctor_cannot_delete_patient()
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $patient = Patient::factory()->create();

        $response = $this->actingAs($doctor)->deleteJson('/api/patients/'.$patient->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('patients', ['id' => $patient->id]);
    }
}
