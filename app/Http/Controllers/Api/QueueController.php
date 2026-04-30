<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicQueueItem;
use App\Models\PatientJourneyEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function today(Request $request)
    {
        $today = Carbon::today();

        $query = ClinicQueueItem::with(['patient', 'doctor', 'appointment'])
            ->whereDate('created_at', $today);

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderByRaw("
    array_position(
        ARRAY['called','in_consultation','waiting','billing','completed','skipped','cancelled'],
        status
    )
")
->orderByRaw("
    array_position(
        ARRAY['vip','elderly','urgent','normal'],
        priority
    )
")
            ->orderBy('created_at', 'asc')
            ->get();

        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'queue_number' => $item->queue_number,
                'patient_name' => $item->patient ? $item->patient->first_name.' '.$item->patient->last_name : 'N/A',
                'doctor_name' => $item->doctor ? 'د. '.$item->doctor->first_name.' '.$item->doctor->last_name : 'N/A',
                'appointment_time' => $item->appointment ? $item->appointment->appointment_time : null,
                'status' => $item->status,
                'priority' => $item->priority,
                'checked_in_at' => $item->appointment ? $item->appointment->checked_in_at : null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    private function updateStatus($id, $status, $timestampField)
    {
        $item = ClinicQueueItem::findOrFail($id);

        $validTransitions = [
            'called' => ['waiting', 'skipped'],
            'in_consultation' => ['called'],
            'billing' => ['in_consultation'],
            'completed' => ['in_consultation', 'billing'],
            'skipped' => ['waiting', 'called'],
        ];

        if (! in_array($item->status, $validTransitions[$status] ?? [])) {
            return response()->json([
                'message' => "لا يمكن تغيير الحالة من {$item->status} إلى {$status}",
            ], 400);
        }

        try {
            DB::beginTransaction();

            $item->status = $status;
            if ($timestampField) {
                $item->$timestampField = Carbon::now();
            }
            $item->save();

            $appointment = $item->appointment;
            if ($appointment) {
                $appointmentStatusMap = [
                    'called' => 'waiting',
                    'in_consultation' => 'in_consultation',
                    'billing' => 'completed',
                    'completed' => 'completed',
                    'skipped' => 'no_show',
                ];

                if (isset($appointmentStatusMap[$status])) {
                    $appointment->status = $appointmentStatusMap[$status];
                    $appointment->save();
                }

                PatientJourneyEvent::create([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'event_type' => $status,
                    'note' => "Queue status updated to {$status}",
                    'created_by' => auth()->id() ?? null,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'تم تحديث الحالة بنجاح', 'data' => $item]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'حدث خطأ أثناء تحديث الحالة', 'error' => $e->getMessage()], 500);
        }
    }

    public function call($id)
    {
        return $this->updateStatus($id, 'called', 'called_at');
    }

    public function start($id)
    {
        return $this->updateStatus($id, 'in_consultation', 'started_at');
    }

    public function billing($id)
    {
        return $this->updateStatus($id, 'billing', 'billing_at');
    }

    public function complete($id)
    {
        return $this->updateStatus($id, 'completed', 'completed_at');
    }

    public function skip($id)
    {
        return $this->updateStatus($id, 'skipped', 'skipped_at');
    }

    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:normal,urgent,elderly,vip',
        ]);

        $item = ClinicQueueItem::findOrFail($id);

        if (in_array($item->status, ['completed', 'cancelled'])) {
            return response()->json(['message' => 'لا يمكن تغيير الأولوية لطلب منتهي'], 400);
        }

        $item->priority = $request->priority;
        $item->save();

        if ($item->appointment) {
            PatientJourneyEvent::create([
                'appointment_id' => $item->appointment->id,
                'patient_id' => $item->appointment->patient_id,
                'event_type' => 'priority_changed',
                'note' => "Priority changed to {$request->priority}",
                'created_by' => auth()->id() ?? null,
            ]);
        }

        return response()->json(['message' => 'تم تحديث الأولوية بنجاح', 'data' => $item]);
    }
}
