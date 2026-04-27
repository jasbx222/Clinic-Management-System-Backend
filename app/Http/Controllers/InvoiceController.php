<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyDiscountRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

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

    public function store(StoreInvoiceRequest $request)
    {
        Gate::authorize('create', Invoice::class);

        $validated = $request->validated();

        $invoice = $this->invoiceService->createInvoice($validated);

        return new InvoiceResource($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $validated = $request->validated();

        $invoice->update($validated);

        return new InvoiceResource($invoice);
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        return new InvoiceResource($invoice->load(['patient', 'appointment', 'visit', 'payments']));
    }

    public function applyDiscount(ApplyDiscountRequest $request, Invoice $invoice)
    {
        Gate::authorize('applyDiscount', Invoice::class);

        $validated = $request->validated();

        try {
            $invoice = $this->invoiceService->applyDiscount($invoice, $validated['discount']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return new InvoiceResource($invoice);
    }

    public function print(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['patient.user', 'appointment', 'visit', 'payments']);

        return view('invoices.print', compact('invoice'));
    }
}
