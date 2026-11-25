<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function changeRole(User $user, User $targetUser): bool
    {
        return $user->isAdmin() && ! $user->is($targetUser);
    }
}
