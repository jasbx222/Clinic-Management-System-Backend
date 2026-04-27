<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request)
    {
        Gate::authorize('create', Payment::class);

        $validated = $request->validated();

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        if ($invoice->status === 'paid') {
            return response()->json(['message' => 'Invoice is already fully paid.'], 400);
        }

        $remainingAmount = $invoice->total - $invoice->paid_amount;

        if ($validated['amount'] > $remainingAmount) {
            return response()->json(['message' => 'Payment amount exceeds remaining balance.'], 400);
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => $request->user()->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $invoice->paid_amount += $validated['amount'];

            if ($invoice->paid_amount >= $invoice->total) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partially_paid';
            }

            $invoice->save();

            DB::commit();

            return new PaymentResource($payment);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Payment $payment)
    {
        Gate::authorize('view', $payment);

        return new PaymentResource($payment->load('invoice'));
    }
}
