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
                'name' => 'Freelance',
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
            [
                'name' => 'Entertainment',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Investment',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Health',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Fruits',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Bills',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Clothes',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Travel',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Education',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Gift',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Pets',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Motor',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Car',
                'type' => CategoryType::Income->value,
            ],
            [
                'name' => 'Home',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Assurance',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Sports',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Social',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Subscription',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Transportation',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Beauty',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' =>  'Electronics',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Shopping',
                'type' => CategoryType::Expense->value,
            ],
            [
                'name' => 'Others',
                'type' => CategoryType::Expense->value,
            ]
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                [
                    'user_id' => null,
                    'name' => $data['name'],
                ],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                ],
            );
        }
    }
}
