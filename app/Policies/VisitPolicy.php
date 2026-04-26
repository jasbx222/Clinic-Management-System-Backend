<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'doctor', 'nurse', 'receptionist']);
    }

    public function view(User $user, Visit $visit)
    {
        if (in_array($user->role, ['admin', 'nurse'])) {
            return true;
        }
        if ($user->role === 'doctor') {
            return $user->id === $visit->doctor_id;
        }

        return $user->id === $visit->patient->user_id;
    }

    public function create(User $user)
    {
        return $user->role === 'doctor' || $user->role === 'admin';
    }

    public function update(User $user, Visit $visit)
    {
        return ($user->role === 'doctor' || $user->role === 'admin') && $user->id === $visit->doctor_id;
    }

    public function delete(User $user, Visit $visit)
    {
        return false; // Prevent deletion of visits
    }
}
