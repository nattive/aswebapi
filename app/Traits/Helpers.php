<?php

namespace App\Traits;

use App\Models\User;

/**
 * All tutor helper methods
 */
trait Helper
{
    public function UserHasAccess(User $user, $role = 'MANAGER')
    {
        return $user->role == $role;
    }

}
