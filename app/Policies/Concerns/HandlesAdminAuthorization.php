<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait HandlesAdminAuthorization
{
    /**
     * Grant all permissions to administrators while letting policies handle others.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }
}
