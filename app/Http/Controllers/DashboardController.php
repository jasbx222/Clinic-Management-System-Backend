<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $patientsQuery = Patient::query();
        $appointmentsQuery = Appointment::query();
        $invoiceQuery = Invoice::query();

        // Scope queries for doctors
        if ($user->role === 'doctor') {
            // A doctor's patients are those who have an appointment or visit with them
            $patientsQuery->whereHas('appointments', function ($q) use ($user) {
                $q->where('doctor_id', $user->id);
            })->orWhereHas('visits', function ($q) use ($user) {
                $q->where('doctor_id', $user->id);
            });

            $appointmentsQuery->where('doctor_id', $user->id);

            // Invoices connected to this doctor's appointments/visits
            $invoiceQuery->where(function($q) use ($user) {
                $q->whereHas('appointment', function ($q2) use ($user) {
                    $q2->where('doctor_id', $user->id);
                })->orWhereHas('visit', function ($q3) use ($user) {
                    $q3->where('doctor_id', $user->id);
                });
            });
        }

        $totalPatients = $patientsQuery->count();

        $appointmentsToday = (clone $appointmentsQuery)->whereDate('appointment_date', $today)->count();
        $pendingAppointments = (clone $appointmentsQuery)->where('status', 'pending')->count();

        // Use paid_amount for revenue calculation if necessary
        $revenueToday = (clone $invoiceQuery)->whereDate('created_at', $today)->where('status', 'paid')->sum('total');

        $recentAppointments = (clone $appointmentsQuery)->with(['patient.user', 'doctor'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'total_patients' => $totalPatients,
            'appointments_today' => $appointmentsToday,
            'pending_appointments' => $pendingAppointments,
            'revenue_today' => $revenueToday,
            'recent_appointments' => $recentAppointments,
        ]);
    }
}
