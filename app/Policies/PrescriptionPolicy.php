<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Prescription $prescription)
    {
        if (in_array($user->role, ['admin', 'nurse', 'receptionist'])) {
            return true;
        }
        if ($user->role === 'doctor' || $user->role === 'admin') {
            return $user->id === $prescription->doctor_id;
        }

        return $user->id === $prescription->patient->user_id;
    }

    public function create(User $user)
    {
        return $user->role === 'doctor' || $user->role === 'admin';
    }

    public function update(User $user, Prescription $prescription)
    {
        return ($user->role === 'doctor' || $user->role === 'admin') && $user->id === $prescription->doctor_id;
    }

    public function delete(User $user, Prescription $prescription)
    {
        return false; // Typically prescriptions aren't deleted
    }
}
