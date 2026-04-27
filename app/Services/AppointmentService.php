<?php

namespace App\Services;

use App\Mail\AppointmentStatusMail;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    /**
     * Create a public appointment (including creating user/patient profiles if needed).
     */
    public function createPublicAppointment(array $validated): Appointment
    {
        // Check if slot is available
        $this->checkConflict($validated['doctor_id'], $validated['date'], $validated['time'].':00');

        return DB::transaction(function () use ($validated) {
            // Find or create user
            $user = User::firstOrCreate(
                ['phone' => $validated['patient']['phone']],
                [
                    'name' => $validated['patient']['full_name'],
                    'role' => 'patient',
                    'email' => $validated['patient']['email'] ?? null,
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

            $appointment = Appointment::create([
                'booking_number' => $bookingNumber,
                'patient_id' => $patientProfile->id,
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'],
                'appointment_date' => $validated['date'],
                'appointment_time' => $validated['time'],
                'status' => 'pending',
                'reason' => $validated['patient']['reason'] ?? '',
            ]);

            return $appointment->load(['doctor', 'service']);
        });
    }

    /**
     * Check if a time slot is already booked for a doctor.
     */
    public function checkConflict(int $doctorId, string $date, string $time): void
    {
        $existing = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'time' => ['هذا الوقت محجوز مسبقاً، يرجى اختيار وقت آخر.'],
            ]);
        }
    }

    /**
     * Create an appointment from the dashboard.
     */
    public function createAppointment(array $validated): Appointment
    {
        $this->checkConflict($validated['doctor_id'], $validated['appointment_date'], $validated['appointment_time']);

        $validated['status'] = 'pending';

        return Appointment::create($validated);
    }

    /**
     * Update an appointment status and optionally send email.
     */
    public function updateStatus(Appointment $appointment, array $validated): Appointment
    {
        $oldStatus = $appointment->status;
        $appointment->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $status = $validated['status'];
            if (in_array($status, ['confirmed', 'cancelled', 'completed'])) {
                $patientEmail = $appointment->patient->user->email ?? null;
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new AppointmentStatusMail($appointment, $status));
                }
            }
        }

        return $appointment;
    }
}
