<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceService
{
    /**
     * Create a new invoice with initial calculations.
     */
    public function createInvoice(array $validated): Invoice
    {
        $validated['tax'] = $validated['tax'] ?? 0;
        $validated['discount'] = 0;
        $validated['total'] = $validated['subtotal'] + $validated['tax'];
        $validated['status'] = 'unpaid';

        return Invoice::create($validated);
    }

    /**
     * Apply discount to an invoice and recalculate totals and status.
     */
    public function applyDiscount(Invoice $invoice, float $discount): Invoice
    {
        if ($discount > $invoice->subtotal) {
            throw new \InvalidArgumentException('Discount cannot be greater than subtotal.');
        }

        $invoice->discount = $discount;
        $invoice->total = ($invoice->subtotal - $invoice->discount) + $invoice->tax;

        // Update status based on paid amount
        if ($invoice->paid_amount >= $invoice->total) {
            $invoice->status = 'paid';
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'partially_paid';
        } else {
            $invoice->status = 'unpaid';
        }

        $invoice->save();

        return $invoice;
    }
}
