<?php

use Phinx\Seed\AbstractSeed;

class FineSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'setting_name' => 'fine_per_day',
                'setting_value' => '5',
                'description' => 'Fine amount per day for overdue books'
            ],
            [
                'setting_name' => 'max_borrow_days',
                'setting_value' => '14',
                'description' => 'Maximum days a book can be borrowed'
            ],
            [
                'setting_name' => 'grace_period_days',
                'setting_value' => '0',
                'description' => 'Grace period before fines start'
            ],
            [
                'setting_name' => 'max_fine_amount',
                'setting_value' => '500',
                'description' => 'Maximum fine amount per book'
            ],
            [
                'setting_name' => 'fine_calculation_method',
                'setting_value' => 'daily',
                'description' => 'Method for calculating fines: daily or fixed'
            ]
        ];

        $this->table('fine_settings')->insert($data)->saveData();
    }
}
