<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Notifications\AppointmentConfirmed;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointmentService) {}

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

    public function store(StoreAppointmentRequest $request)
    {
        Gate::authorize('create', Appointment::class);

        $validated = $request->validated();

        $appointment = $this->appointmentService->createAppointment($validated);

        // Notify patient
        // $appointment->patient->user->notify(new AppointmentConfirmed($appointment));

        return new AppointmentResource($appointment);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        $validated = $request->validated();

        $appointment = $this->appointmentService->updateStatus($appointment, $validated);

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
