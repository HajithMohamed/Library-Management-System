<?php

use Phinx\Seed\AbstractSeed;

class SystemSettingsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'settingKey' => 'library_name',
                'settingValue' => 'University Central Library',
                'settingType' => 'string',
                'description' => 'Name of the library'
            ],
            [
                'settingKey' => 'library_email',
                'settingValue' => 'library@university.edu',
                'settingType' => 'string',
                'description' => 'Library contact email'
            ],
            [
                'settingKey' => 'library_phone',
                'settingValue' => '+1-555-0100',
                'settingType' => 'string',
                'description' => 'Library contact phone'
            ],
            [
                'settingKey' => 'max_books_per_user',
                'settingValue' => '5',
                'settingType' => 'number',
                'description' => 'Maximum books a user can borrow'
            ],
            [
                'settingKey' => 'enable_notifications',
                'settingValue' => 'true',
                'settingType' => 'boolean',
                'description' => 'Enable system notifications'
            ]
        ];

        $this->table('system_settings')->insert($data)->saveData();
    }
}
