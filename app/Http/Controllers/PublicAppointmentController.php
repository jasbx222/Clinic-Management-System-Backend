<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicAppointmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'patient.full_name' => 'required|string|max:255',
            'patient.phone' => 'required|string|max:20',
            'patient.gender' => 'required|in:male,female,other',
            'patient.birth_date' => 'required|date|before:today',
            'patient.reason' => 'nullable|string',
            'patient.notes' => 'nullable|string',
        ]);

        // Check if slot is available
        $existing = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['date'])
            ->where('appointment_time', $validated['time'].':00')
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'time' => ['هذا الوقت محجوز مسبقاً، يرجى اختيار وقت آخر.'],
            ]);
        }

        $appointment = DB::transaction(function () use ($validated) {
            // Find or create user
            $user = User::firstOrCreate(
                ['phone' => $validated['patient']['phone']],
                [
                    'name' => $validated['patient']['full_name'],
                    'role' => 'patient',
                    'email' => $validated['patient']['phone'].'@patient.clinic.com',
                    'password' => bcrypt('password'), // default password for now
                ]
            );

            // Find or create patient profile
            $patientProfile = Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'file_number' => 'PT-'.now()->format('Ymd').'-'.rand(100, 999),
                    'date_of_birth' => $validated['patient']['birth_date'],
                    'gender' => $validated['patient']['gender'],
                ]
            );

            // Create appointment
            $bookingNumber = 'APT-'.now()->format('Ymd').'-'.rand(1000, 9999);

            $app = Appointment::create([
                'booking_number' => $bookingNumber,
                'patient_id' => $patientProfile->id,
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'],
                'appointment_date' => $validated['date'],
                'appointment_time' => $validated['time'],
                'status' => 'pending',
                'reason' => $validated['patient']['reason'] ?? '',
            ]);

            return $app->load(['doctor', 'service']);
        });

        return response()->json([
            'data' => [
                'appointment_id' => $appointment->id,
                'booking_number' => $appointment->booking_number,
                'status' => $appointment->status,
                'doctor_name' => $appointment->doctor->name ?? 'طبيب',
                'service_name' => $appointment->service->name ?? 'خدمة',
                'date' => $appointment->appointment_date,
                'time' => substr($appointment->appointment_time, 0, 5),
            ],
        ], 201);
    }
}
