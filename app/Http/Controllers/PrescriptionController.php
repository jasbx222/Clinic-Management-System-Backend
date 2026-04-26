<?php

namespace App\Http\Controllers;

use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PrescriptionController extends Controller
{
    public function store(Request $request)
    {
        Gate::authorize('create', Prescription::class);

        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.dosage' => 'required|string',
            'items.*.frequency' => 'required|string',
            'items.*.duration' => 'required|string',
            'items.*.instructions' => 'nullable|string',
        ]);

        $visit = Visit::findOrFail($validated['visit_id']);

        DB::beginTransaction();
        try {
            $prescription = Prescription::create([
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'doctor_id' => $visit->doctor_id,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $prescription->items()->create($item);
            }

            DB::commit();

            return new PrescriptionResource($prescription->load('items'));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Prescription $prescription)
    {
        Gate::authorize('view', $prescription);

        return new PrescriptionResource($prescription->load(['items', 'patient', 'doctor']));
    }
}
