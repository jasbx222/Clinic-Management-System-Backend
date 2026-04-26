<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Mail\AppointmentStatusMail;
use App\Models\Appointment;
use App\Notifications\AppointmentConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Appointment::class);

        $query = Appointment::with(['patient.user', 'doctor', 'service']);

        if ($request->user()->role === 'doctor') {
            $query->where('doctor_id', $request->user()->id);
        } elseif ($request->user()->role === 'patient') {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        if ($request->has('date')) {
            $query->where('appointment_date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date')->orderBy('appointment_time')->paginate(15);

        return AppointmentResource::collection($appointments);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Appointment::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'nullable|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string',
        ]);

        // Check for conflict
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'appointment_time' => ['This time slot is already booked for this doctor.'],
            ]);
        }

        $validated['status'] = 'pending';
        $appointment = Appointment::create($validated);

        // Notify patient
        // $appointment->patient->user->notify(new AppointmentConfirmed($appointment));

        return new AppointmentResource($appointment);
    }

    public function update(Request $request, Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,arrived,waiting,in_consultation,completed,cancelled,no_show',
        ]);

        $oldStatus = $appointment->status;
        $appointment->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $status = $validated['status'];
            if (in_array($status, ['confirmed', 'cancelled', 'completed'])) {
                $patientEmail = $appointment->patient->user->email;
                if ($patientEmail) {
                    Mail::to($patientEmail)->send(new AppointmentStatusMail($appointment, $status));
                }
            }
        }

        return new AppointmentResource($appointment);
    }

    public function show(Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return new AppointmentResource($appointment->load(['patient.user', 'doctor', 'service']));
    }

    public function cancel(Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        if ($appointment->status === 'completed' || $appointment->status === 'in_consultation') {
            return response()->json(['message' => 'Cannot cancel a completed or active appointment.'], 400);
        }

        $appointment->update(['status' => 'cancelled']);

        return new AppointmentResource($appointment);
    }

    public function arrive(Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        $appointment->update(['status' => 'waiting']);

        return new AppointmentResource($appointment);
    }
}
