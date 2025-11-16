<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::first();

        if (! $owner) {
            $this->command?->warn('Skipping AccountSeeder because no users exist yet.');

            return;
        }

        $accounts = [
            [
                'name' => 'Cash Wallet',
                'type' => AccountType::Cash,
                'details' => [
                    'initial_balance' => 0,
                    'is_active' => true,
                    'is_primary' => true,
                ],
            ],
            [
                'name' => 'Primary Bank Account',
                'type' => AccountType::Bank,
                'details' => [
                    'initial_balance' => 0,
                    'is_active' => true,
                    'is_primary' => false,
                ],
            ],
        ];

        foreach ($accounts as $data) {
            /** @var Account $account */
            $account = Account::updateOrCreate(
                ['name' => $data['name']],
                [
                    'type' => $data['type']->value,
                ],
            );

            AccountUser::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'user_id' => $owner->id,
                ],
                [
                    'initial_balance' => $data['details']['initial_balance'],
                    'is_active' => $data['details']['is_active'],
                    'is_primary' => $data['details']['is_primary'],
                ],
            );
        }
    }

}
