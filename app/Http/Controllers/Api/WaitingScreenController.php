<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicQueueItem;
use Carbon\Carbon;

class WaitingScreenController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $queueItems = ClinicQueueItem::with('doctor')
            ->whereDate('created_at', $today)
            ->whereIn('status', ['waiting', 'called', 'in_consultation'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('doctor_id');

        $data = [];

        foreach ($queueItems as $doctorId => $items) {
            $doctor = $items->first()->doctor;
            if (! $doctor) {
                continue;
            }

            $current = $items->whereIn('status', ['called', 'in_consultation'])->sortByDesc('updated_at')->first();
            $nextItems = $items->where('status', 'waiting')->take(3);

            if ($current || $nextItems->count() > 0) {
                $data[] = [
                    'doctor_name' => 'د. '.$doctor->first_name.' '.$doctor->last_name,
                    'current' => $current ? [
                        'queue_number' => $current->queue_number,
                        'status' => $current->status,
                    ] : null,
                    'next' => $nextItems->values()->map(function ($item) {
                        return ['queue_number' => $item->queue_number];
                    })->toArray(),
                ];
            }
        }

        return response()->json(['data' => $data]);
    }
}
