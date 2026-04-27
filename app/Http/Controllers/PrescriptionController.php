<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PrescriptionController extends Controller
{
    public function store(StorePrescriptionRequest $request)
    {
        Gate::authorize('create', Prescription::class);

        $validated = $request->validated();

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
