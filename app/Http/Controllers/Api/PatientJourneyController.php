<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientJourneyEvent;

class PatientJourneyController extends Controller
{
    public function show(Appointment $appointment)
    {
        $events = PatientJourneyEvent::with('creator')
            ->where('appointment_id', $appointment->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $data = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'note' => $event->note,
                'created_at' => $event->created_at->format('Y-m-d H:i:s'),
                'created_by_name' => $event->creator ? $event->creator->first_name.' '.$event->creator->last_name : null,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
