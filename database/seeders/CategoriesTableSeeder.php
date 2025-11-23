<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Salary',
                'type' => CategoryType::Income->value,
            ],
            [
                'name' => 'Food',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Transport',
                'type' => CategoryType::Expense->value,
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                [
                    'user_id' => null,
                    'name' => $data['name'],
                ],
                [
                    'type' => $data['type'],
                ],
            );
        }
    }
}
