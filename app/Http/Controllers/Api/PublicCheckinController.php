<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicQueueItem;
use App\Models\PatientJourneyEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicCheckinController extends Controller
{
    public function show($token)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->where('checkin_token', $token)->first();

        if (! $appointment) {
            return response()->json(['message' => 'رابط تسجيل الوصول غير صالح'], 404);
        }

        if ($appointment->status === 'cancelled') {
            return response()->json(['message' => 'هذا الموعد ملغي ولا يمكن تسجيل الوصول'], 400);
        }

        if (in_array($appointment->status, ['completed', 'arrived', 'waiting', 'in_consultation'])) {
            $existingQueue = ClinicQueueItem::where('appointment_id', $appointment->id)->first();
            if ($existingQueue) {
                return response()->json([
                    'data' => [
                        'already_checked_in' => true,
                        'queue_number' => $existingQueue->queue_number,
                        'status' => $existingQueue->status,
                    ],
                ]);
            }

            return response()->json(['message' => 'تم تسجيل وصولك مسبقاً'], 400);
        }

        return response()->json([
            'data' => [
                'patient_name' => $appointment->patient->first_name.' '.$appointment->patient->last_name,
                'doctor_name' => 'د. '.$appointment->doctor->first_name.' '.$appointment->doctor->last_name,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time,
                'status' => $appointment->status,
                'can_checkin' => true,
            ],
        ]);
    }

    public function confirm($token)
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::where('checkin_token', $token)->lockForUpdate()->first();

            if (! $appointment) {
                DB::rollBack();

                return response()->json(['message' => 'رابط تسجيل الوصول غير صالح'], 404);
            }

            if ($appointment->status === 'cancelled') {
                DB::rollBack();

                return response()->json(['message' => 'هذا الموعد ملغي ولا يمكن تسجيل الوصول'], 400);
            }

            if (in_array($appointment->status, ['completed', 'arrived', 'waiting', 'in_consultation'])) {
                $existingQueue = ClinicQueueItem::where('appointment_id', $appointment->id)->first();
                DB::rollBack();

                if ($existingQueue) {
                    return response()->json([
                        'message' => 'تم تسجيل وصولك مسبقاً',
                        'data' => [
                            'queue_number' => $existingQueue->queue_number,
                            'status' => $existingQueue->status,
                        ],
                    ]);
                }

                return response()->json(['message' => 'تم تسجيل وصولك مسبقاً'], 400);
            }

            $appointment->status = 'waiting';
            $appointment->checked_in_at = Carbon::now();
            $appointment->save();

            $queueItem = ClinicQueueItem::where('appointment_id', $appointment->id)->first();

            if (! $queueItem) {
                $today = Carbon::today();
                $count = ClinicQueueItem::whereDate('created_at', $today)->count();
                $queueNumber = 'A-'.str_pad($count + 1, 3, '0', STR_PAD_LEFT);

                $queueItem = ClinicQueueItem::create([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'queue_number' => $queueNumber,
                    'status' => 'waiting',
                    'priority' => 'normal',
                ]);
            }

            PatientJourneyEvent::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'event_type' => 'checked_in',
                'note' => 'Patient checked in via public link',
            ]);

            PatientJourneyEvent::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'event_type' => 'waiting',
                'note' => 'Patient added to queue',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم تسجيل وصولك بنجاح',
                'data' => [
                    'queue_number' => $queueItem->queue_number,
                    'status' => $queueItem->status,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'حدث خطأ أثناء تسجيل الوصول', 'error' => $e->getMessage()], 500);
        }
    }
}
