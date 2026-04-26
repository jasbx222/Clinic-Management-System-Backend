<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_revenue_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Invoice::factory()->create(['total' => 100, 'paid_amount' => 100, 'status' => 'paid']);
        Invoice::factory()->create(['total' => 200, 'paid_amount' => 50, 'status' => 'partially_paid']);

        $response = $this->actingAs($admin)->getJson('/api/reports/revenue');

        $response->assertStatus(200)
            ->assertJson([
                'total_revenue' => 150,
                'total_invoiced' => 300,
                'outstanding' => 150,
            ]);
    }

    public function test_doctor_cannot_view_reports()
    {
        $doctor = User::factory()->create(['role' => 'doctor']);

        $response = $this->actingAs($doctor)->getJson('/api/reports/revenue');

        $response->assertStatus(403);
    }
}
