<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\UserAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAccount>
 */
class UserAccountFactory extends Factory
{
    protected $model = UserAccount::class;

    public function definition(): array
    {
        $initialBalance = $this->faker->randomFloat(2, 0, 5_000);

        return [
            'account_id' => Account::factory(),
            'user_id' => User::factory(),
            'initial_balance' => $initialBalance,
            'balance' => $initialBalance,
            'is_primary' => $this->faker->boolean(20),
        ];
    }
}
