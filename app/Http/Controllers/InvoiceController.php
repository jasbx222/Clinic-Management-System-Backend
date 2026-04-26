<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $query = Invoice::with(['patient.user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->user()->role === 'patient') {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        } elseif ($request->user()->role === 'doctor') {
            $query->where(function ($q) use ($request) {
                $q->whereHas('appointment', function ($q2) use ($request) {
                    $q2->where('doctor_id', $request->user()->id);
                })->orWhereHas('visit', function ($q3) use ($request) {
                    $q3->where('doctor_id', $request->user()->id);
                });
            });
        }

        return InvoiceResource::collection($query->paginate(15));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Invoice::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_id' => 'nullable|exists:visits,id',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['tax'] = $validated['tax'] ?? 0;
        $validated['discount'] = 0;
        $validated['total'] = $validated['subtotal'] + $validated['tax'];
        $validated['status'] = 'unpaid';

        $invoice = Invoice::create($validated);

        return new InvoiceResource($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $validated = $request->validate([
            'status' => 'sometimes|in:unpaid,partially_paid,paid,cancelled',
            'notes' => 'sometimes|string',
        ]);

        $invoice->update($validated);

        return new InvoiceResource($invoice);
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        return new InvoiceResource($invoice->load(['patient', 'appointment', 'visit', 'payments']));
    }

    public function applyDiscount(Request $request, Invoice $invoice)
    {
        Gate::authorize('applyDiscount', Invoice::class);

        $validated = $request->validate([
            'discount' => 'required|numeric|min:0',
        ]);

        if ($validated['discount'] > $invoice->subtotal) {
            return response()->json(['message' => 'Discount cannot be greater than subtotal.'], 400);
        }

        $invoice->discount = $validated['discount'];
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

        return new InvoiceResource($invoice);
    }

    public function print(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['patient.user', 'appointment', 'visit', 'payments']);
        
        return view('invoices.print', compact('invoice'));
    }
}
