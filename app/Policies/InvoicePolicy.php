<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'accountant', 'receptionist', 'patient']);
    }

    public function view(User $user, Invoice $invoice)
    {
        if (in_array($user->role, ['admin', 'accountant', 'receptionist'])) {
            return true;
        }

        return $user->id === $invoice->patient->user_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'accountant', 'receptionist']);
    }

    public function update(User $user, Invoice $invoice)
    {
        return in_array($user->role, ['admin', 'accountant']);
    }

    public function applyDiscount(User $user)
    {
        return in_array($user->role, ['admin', 'accountant']);
    }

    public function delete(User $user, Invoice $invoice)
    {
        return $user->role === 'admin';
    }
}
