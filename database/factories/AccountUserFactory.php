<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountUser>
 */
class AccountUserFactory extends Factory
{
    protected $model = AccountUser::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'initial_balance' => $this->faker->randomFloat(2, 0, 5_000),
            'is_active' => $this->faker->boolean(80),
            'is_primary' => $this->faker->boolean(20),
        ];
    }
}
