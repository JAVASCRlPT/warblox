<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    /**
     * Check if user is admin.
     */
    public function admin(User $user): bool
    {
        return $user->role === 'admin';
    }
}
