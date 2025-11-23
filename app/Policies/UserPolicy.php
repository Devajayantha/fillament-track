<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\HandlesAdminAuthorization;

class UserPolicy
{
    use HandlesAdminAuthorization;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $record): bool
    {
        return $user->is($record);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $record): bool
    {
        return $user->is($record);
    }

    public function delete(User $user, User $record): bool
    {
        return false;
    }
}
