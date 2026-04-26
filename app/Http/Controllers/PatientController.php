<?php

namespace App\Http\Controllers;

use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
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

    public function store(Request $request)
    {
        Gate::authorize('create', Patient::class);

        $validated = $request->validate([
            'name' => 'required_without:user_id|string|max:255',
            'phone' => 'required_without:user_id|string|max:255',
            'email' => 'nullable|email',
            'user_id' => 'nullable|exists:users,id',
            'date_of_birth' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if (empty($validated['user_id'])) {
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'role' => 'patient',
                ]);
                $validated['user_id'] = $user->id;
            }

            $validated['date_of_birth'] = $validated['date_of_birth'] ?? $validated['birth_date'] ?? null;
            $validated['file_number'] = 'PT-'.strtoupper(uniqid());

            $patient = Patient::create($validated);

            \Illuminate\Support\Facades\DB::commit();

            return new PatientResource($patient);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);

        $patient->load(['user', 'visits.doctor', 'prescriptions', 'invoices']);

        return new PatientResource($patient);
    }

    public function update(Request $request, Patient $patient)
    {
        Gate::authorize('update', $patient);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'date_of_birth' => 'sometimes|date',
            'birth_date' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'blood_group' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
        ]);

        if (isset($validated['name']) || isset($validated['phone']) || isset($validated['email'])) {
            $patient->user->update([
                'name' => $validated['name'] ?? $patient->user->name,
                'phone' => $validated['phone'] ?? $patient->user->phone,
                'email' => $validated['email'] ?? $patient->user->email,
            ]);
        }

        if (isset($validated['birth_date'])) {
            $validated['date_of_birth'] = $validated['birth_date'];
        }

        $patient->update($validated);

        return new PatientResource($patient);
    }

    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);

        $patient->delete();

        return response()->json(['message' => 'Patient deleted successfully.']);
    }
}
