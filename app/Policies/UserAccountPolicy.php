<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAccount;
use App\Policies\Concerns\HandlesAdminAuthorization;

class UserAccountPolicy
{
    use HandlesAdminAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, UserAccount $userAccount): bool
    {
        return $userAccount->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserAccount $userAccount): bool
    {
        return $userAccount->user_id === $user->id;
    }

    public function delete(User $user, UserAccount $userAccount): bool
    {
        return $userAccount->user_id === $user->id;
    }
}
