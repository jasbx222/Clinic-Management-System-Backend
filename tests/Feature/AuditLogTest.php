<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_is_created_on_model_creation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $patientUser = User::factory()->create(['role' => 'patient']);

        $patient = Patient::create([
            'user_id' => $patientUser->id,
            'file_number' => 'TEST-01',
            'date_of_birth' => '2000-01-01',
            'gender' => 'male',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'create',
            'model_type' => Patient::class,
            'model_id' => $patient->id,
        ]);
    }

    public function test_audit_log_records_old_and_new_values_on_update()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $patient = Patient::factory()->create([
            'blood_group' => 'A+',
        ]);

        $patient->update(['blood_group' => 'O+']);

        $log = AuditLog::where('model_type', Patient::class)
            ->where('model_id', $patient->id)
            ->where('action', 'update')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('A+', $log->old_values['blood_group']);
        $this->assertEquals('O+', $log->new_values['blood_group']);
    }
}
