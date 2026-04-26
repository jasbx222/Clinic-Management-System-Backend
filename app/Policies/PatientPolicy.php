<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'receptionist', 'doctor', 'nurse']);
    }

    public function view(User $user, Patient $patient)
    {
        if (in_array($user->role, ['admin', 'receptionist', 'doctor', 'nurse'])) {
            return true;
        }

        return $user->id === $patient->user_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function update(User $user, Patient $patient)
    {
        if (in_array($user->role, ['admin', 'receptionist'])) {
            return true;
        }

        return $user->id === $patient->user_id;
    }

    public function delete(User $user, Patient $patient)
    {
        return $user->role === 'admin';
    }
}
