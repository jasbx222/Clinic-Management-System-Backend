<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user)
    {
        return true; // Everyone can view their own, logic is in controller
    }

    public function view(User $user, Appointment $appointment)
    {
        if (in_array($user->role, ['admin', 'receptionist'])) {
            return true;
        }
        if ($user->role === 'doctor') {
            return $user->id === $appointment->doctor_id;
        }

        return $user->id === $appointment->patient->user_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'receptionist', 'patient']);
    }

    public function update(User $user, Appointment $appointment)
    {
        if (in_array($user->role, ['admin', 'receptionist'])) {
            return true;
        }
        if ($user->role === 'doctor') {
            return $user->id === $appointment->doctor_id;
        }

        return $user->id === $appointment->patient->user_id; // Patients can reschedule/cancel
    }

    public function delete(User $user, Appointment $appointment)
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }
}
