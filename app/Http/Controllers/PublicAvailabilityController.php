<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicAvailabilityRequest;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\Service;
use Carbon\Carbon;

class PublicAvailabilityController extends Controller
{
    public function index(PublicAvailabilityRequest $request)
    {
        $validated = $request->validated();

        $date = Carbon::parse($request->date);
        $dayName = $date->format('l');

        $schedule = DoctorSchedule::where('doctor_id', $request->doctor_id)
            ->where('day_of_week', $dayName)
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return response()->json(['data' => []]);
        }

        $service = Service::find($request->service_id);
        $duration = $service->duration_minutes ?? 30;

        $bookedAppointments = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->whereNotIn('status', ['cancelled'])
            ->pluck('appointment_time')
            ->map(fn ($time) => substr($time, 0, 5))
            ->toArray();

        $slots = [];
        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $timeStr = $start->format('H:i');
            $available = ! in_array($timeStr, $bookedAppointments);

            // If today, check if time has passed
            if ($date->isToday() && $start->isPast()) {
                $available = false;
            }

            if ($available) {
                $slots[] = [
                    'time' => $timeStr,
                    'label' => $start->format('h:i A'),
                    'available' => true,
                ];
            }

            $start->addMinutes($duration);
        }

        return response()->json(['data' => $slots]);
    }
}
