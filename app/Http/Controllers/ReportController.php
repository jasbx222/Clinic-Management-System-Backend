<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function appointments(Request $request)
    {
        Gate::authorize('viewReports', User::class); // using general policy

        $query = Appointment::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('appointment_date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $total = $query->count();
        $byStatus = clone $query;
        $statusCounts = $byStatus->select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        return response()->json([
            'total_appointments' => $total,
            'status_breakdown' => $statusCounts,
        ]);
    }

    public function revenue(Request $request)
    {
        Gate::authorize('viewReports', User::class);

        $query = Invoice::whereIn('status', ['paid', 'partially_paid']);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date.' 00:00:00', $request->end_date.' 23:59:59']);
        }

        $totalRevenue = $query->sum('paid_amount');
        $totalInvoiced = $query->sum('total');

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_invoiced' => $totalInvoiced,
            'outstanding' => $totalInvoiced - $totalRevenue,
        ]);
    }

    public function doctorsPerformance(Request $request)
    {
        Gate::authorize('viewReports', User::class);

        $query = Appointment::where('status', 'completed');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('appointment_date', [$request->start_date, $request->end_date]);
        }

        $performance = $query->select('doctor_id', \DB::raw('count(*) as completed_appointments'))
            ->with('doctor:id,name')
            ->groupBy('doctor_id')
            ->get();

        return response()->json($performance);
    }
}
