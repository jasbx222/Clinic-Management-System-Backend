<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function __construct(private PatientService $patientService) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Patient::class);

        $query = Patient::with('user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q2) use ($search) {
                $q2->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('file_number', 'like', "%{$search}%");
            });
        }

        if ($request->user()->role === 'doctor') {
            $query->where(function ($q) use ($request) {
                $q->whereHas('appointments', function ($q2) use ($request) {
                    $q2->where('doctor_id', $request->user()->id);
                })->orWhereHas('visits', function ($q3) use ($request) {
                    $q3->where('doctor_id', $request->user()->id);
                });
            });
        }

        if ($request->user()->role === 'patient') {
            $query->where('user_id', $request->user()->id);
        }

        $patients = $query->paginate($request->get('per_page', 15));

        return PatientResource::collection($patients);
    }

    public function store(StorePatientRequest $request)
    {
        Gate::authorize('create', Patient::class);

        $validated = $request->validated();

        $patient = $this->patientService->createPatient($validated);

        return new PatientResource($patient);
    }

    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);

        $patient->load(['user', 'visits.doctor', 'prescriptions', 'invoices']);

        return new PatientResource($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        Gate::authorize('update', $patient);

        $validated = $request->validated();

        $patient = $this->patientService->updatePatient($patient, $validated);

        return new PatientResource($patient);
    }

    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);

        $patient->delete();

        return response()->json(['message' => 'Patient deleted successfully.']);
    }
}
