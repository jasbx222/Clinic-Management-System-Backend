<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'accountant', 'receptionist']);
    }

    public function view(User $user, Payment $payment)
    {
        if (in_array($user->role, ['admin', 'accountant', 'receptionist'])) {
            return true;
        }

        return $user->id === $payment->invoice->patient->user_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'accountant', 'receptionist']);
    }

    public function update(User $user, Payment $payment)
    {
        return false; // Payments usually aren't updated, they are refunded via new entries
    }

    public function delete(User $user, Payment $payment)
    {
        return $user->role === 'admin';
    }
}
