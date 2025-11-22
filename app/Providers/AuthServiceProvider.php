<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAccount;
use App\Policies\AccountPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserAccountPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Account::class => AccountPolicy::class,
        UserAccount::class => UserAccountPolicy::class,
        Category::class => CategoryPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
