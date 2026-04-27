<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    /**
     * Create a new patient and associated user if necessary.
     */
    public function createPatient(array $validated): Patient
    {
        return DB::transaction(function () use ($validated) {
            if (empty($validated['user_id'])) {
                $user = User::create([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'password' => Hash::make('password'),
                    'role' => 'patient',
                ]);
                $validated['user_id'] = $user->id;
            }

            $validated['date_of_birth'] = $validated['date_of_birth'] ?? $validated['birth_date'] ?? null;
            $validated['file_number'] = 'PT-'.strtoupper(uniqid());

            return Patient::create($validated);
        });
    }

    /**
     * Update an existing patient and associated user.
     */
    public function updatePatient(Patient $patient, array $validated): Patient
    {
        return DB::transaction(function () use ($patient, $validated) {
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

            return $patient;
        });
    }
}
