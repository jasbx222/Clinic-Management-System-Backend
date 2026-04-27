<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class VisitService
{
    /**
     * Create a new visit and handle associated appointment status.
     */
    public function createVisit(array $validated, ?int $authDoctorId = null): Visit
    {
        return DB::transaction(function () use ($validated, $authDoctorId) {
            $appointment = null;
            $patientId = $validated['patient_id'] ?? null;
            $doctorId = $validated['doctor_id'] ?? $authDoctorId;

            if (! empty($validated['appointment_id'])) {
                $appointment = Appointment::findOrFail($validated['appointment_id']);
                $appointment->update(['status' => 'in_consultation']);
                $patientId = $appointment->patient_id;
                $doctorId = $appointment->doctor_id;
            }

            return Visit::create([
                'appointment_id' => $appointment ? $appointment->id : null,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'start_time' => now(),
                'chief_complaint' => $validated['chief_complaint'],
                'history' => $validated['history'] ?? null,
                'examination' => $validated['examination'] ?? null,
                'status' => 'in_progress',
            ]);
        });
    }

    /**
     * End a visit and handle associated appointment status.
     */
    public function endVisit(Visit $visit): Visit
    {
        return DB::transaction(function () use ($visit) {
            $visit->update([
                'end_time' => now(),
                'status' => 'completed',
            ]);

            if ($visit->appointment) {
                $visit->appointment->update(['status' => 'completed']);
            }

            return $visit;
        });
    }
}
