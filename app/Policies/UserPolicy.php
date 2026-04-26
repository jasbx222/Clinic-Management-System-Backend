<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewReports(User $user)
    {
        return in_array($user->role, ['admin', 'accountant']);
    }
}
