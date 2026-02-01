<?php

use Phinx\Seed\AbstractSeed;

class AdminSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'userId' => 'ADM001',
                'username' => 'admin',
                'password' => password_hash('admin_password_2026', PASSWORD_BCRYPT, ['cost' => 12]),
                'userType' => 'Admin',
                'emailId' => 'admin@university.edu',
                'isVerified' => 1,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
            ],
            [
                'userId' => 'ADM002',
                'username' => 'superadmin',
                'password' => password_hash('super_secure_admin_123', PASSWORD_BCRYPT, ['cost' => 12]),
                'userType' => 'Admin',
                'emailId' => 'superadmin@university.edu',
                'isVerified' => 1,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
            ]
        ];

        $posts = $this->table('users');
        $posts->insert($data)
            ->saveData();
    }
}
