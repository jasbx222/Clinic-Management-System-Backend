<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_accountant_can_create_invoice()
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        $patient = Patient::factory()->create();

        $response = $this->actingAs($accountant)->postJson('/api/invoices', [
            'patient_id' => $patient->id,
            'subtotal' => 100.00,
            'tax' => 15.00,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id,
            'total' => 115.00,
            'status' => 'unpaid',
        ]);
    }

    public function test_accountant_can_apply_discount()
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        $invoice = Invoice::factory()->create([
            'subtotal' => 100.00,
            'tax' => 10.00,
            'total' => 110.00,
            'discount' => 0,
            'paid_amount' => 0,
        ]);

        $response = $this->actingAs($accountant)->postJson("/api/invoices/{$invoice->id}/apply-discount", [
            'discount' => 20.00,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'discount' => 20.00,
            'total' => 90.00, // 100 - 20 + 10
        ]);
    }

    public function test_payment_updates_invoice_status_to_partially_paid()
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        $invoice = Invoice::factory()->create([
            'subtotal' => 100.00,
            'tax' => 0,
            'total' => 100.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($accountant)->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 40.00,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'paid_amount' => 40.00,
            'status' => 'partially_paid',
        ]);
    }

    public function test_prevent_overpayment()
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        $invoice = Invoice::factory()->create([
            'subtotal' => 100.00,
            'tax' => 0,
            'total' => 100.00,
            'paid_amount' => 80.00,
        ]);

        $response = $this->actingAs($accountant)->postJson('/api/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 50.00, // Exceeds remaining 20
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(400);
    }
}
