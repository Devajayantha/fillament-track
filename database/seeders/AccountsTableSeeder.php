<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Primary Account',
                'type' => AccountType::Bank->value,
                'is_primary' => true,
            ],
            [
                'name' => 'Cash Wallet',
                'type' => AccountType::Cash->value,
                'is_primary' => false,
            ],
        ];

        foreach ($accounts as $data) {
            Account::updateOrCreate(
                ['name' => $data['name']],
                [
                    'type' => $data['type'],
                    'is_primary' => $data['is_primary'],
                ],
            );
        }
    }
}
