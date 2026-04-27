<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicAppointmentRequest;
use App\Services\AppointmentService;

class PublicAppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointmentService) {}

    public function store(StorePublicAppointmentRequest $request)
    {
        $validated = $request->validated();

        $appointment = $this->appointmentService->createPublicAppointment($validated);

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
