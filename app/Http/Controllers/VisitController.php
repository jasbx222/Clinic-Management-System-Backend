<?php

namespace App\Http\Controllers;

use App\Http\Resources\VisitResource;
use App\Models\Appointment;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Visit::class);

        $query = Visit::with(['patient.user', 'doctor']);

        if ($request->user()->role === 'doctor') {
            $query->where('doctor_id', $request->user()->id);
        } elseif ($request->user()->role === 'patient') {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        return VisitResource::collection($query->paginate(15));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Visit::class);

        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_id' => 'required_without:appointment_id|exists:patients,id',
            'doctor_id' => 'nullable|exists:users,id',
            'chief_complaint' => 'required|string',
            'history' => 'nullable|string',
            'examination' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $appointment = null;
            $patientId = $validated['patient_id'] ?? null;
            $doctorId = $validated['doctor_id'] ?? $request->user()->id;

            if (!empty($validated['appointment_id'])) {
                $appointment = Appointment::findOrFail($validated['appointment_id']);
                $appointment->update(['status' => 'in_consultation']);
                $patientId = $appointment->patient_id;
                $doctorId = $appointment->doctor_id;
            }

            $visit = Visit::create([
                'appointment_id' => $appointment ? $appointment->id : null,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'start_time' => now(),
                'chief_complaint' => $validated['chief_complaint'],
                'history' => $validated['history'] ?? null,
                'examination' => $validated['examination'] ?? null,
                'status' => 'in_progress',
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return new VisitResource($visit);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, Visit $visit)
    {
        Gate::authorize('update', $visit);

        if ($visit->status === 'completed') {
            return response()->json(['message' => 'Cannot modify a completed visit.'], 400);
        }

        $validated = $request->validate([
            'chief_complaint' => 'sometimes|string',
            'history' => 'sometimes|string',
            'examination' => 'sometimes|string',
            'diagnosis' => 'sometimes|string',
            'treatment_plan' => 'sometimes|string',
        ]);

        $visit->update($validated);

        return new VisitResource($visit);
    }

    public function show(Visit $visit)
    {
        Gate::authorize('view', $visit);

        return new VisitResource($visit->load(['patient.user', 'doctor', 'prescription', 'invoice']));
    }

    public function endVisit(Visit $visit)
    {
        Gate::authorize('update', $visit);

        DB::beginTransaction();
        try {
            $visit->update([
                'end_time' => now(),
                'status' => 'completed',
            ]);

            if ($visit->appointment) {
                $visit->appointment->update(['status' => 'completed']);
            }

            DB::commit();

            return new VisitResource($visit);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
