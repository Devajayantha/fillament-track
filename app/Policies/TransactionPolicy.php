<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAuthorization;

class TransactionPolicy
{
    use HandlesAdminAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }
}
