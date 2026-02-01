<?php

use Phinx\Seed\AbstractSeed;

class RolePermissionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            ['role' => 'Student', 'permission' => 'view_catalog', 'canRead' => 1, 'canWrite' => 0, 'canDelete' => 0, 'canApprove' => 0, 'description' => 'Can view library catalog'],
            ['role' => 'Student', 'permission' => 'borrow_book', 'canRead' => 1, 'canWrite' => 1, 'canDelete' => 0, 'canApprove' => 0, 'description' => 'Can borrow books'],
            ['role' => 'Librarian', 'permission' => 'manage_books', 'canRead' => 1, 'canWrite' => 1, 'canDelete' => 1, 'canApprove' => 0, 'description' => 'Can manage books'],
            ['role' => 'Librarian', 'permission' => 'approve_requests', 'canRead' => 1, 'canWrite' => 0, 'canDelete' => 0, 'canApprove' => 1, 'description' => 'Can approve borrow requests'],
            ['role' => 'Admin', 'permission' => 'manage_users', 'canRead' => 1, 'canWrite' => 1, 'canDelete' => 1, 'canApprove' => 0, 'description' => 'Can manage all users'],
            ['role' => 'Admin', 'permission' => 'view_reports', 'canRead' => 1, 'canWrite' => 0, 'canDelete' => 0, 'canApprove' => 0, 'description' => 'Can view system reports'],
            ['role' => 'Admin', 'permission' => 'system_settings', 'canRead' => 1, 'canWrite' => 1, 'canDelete' => 0, 'canApprove' => 0, 'description' => 'Can manage system settings']
        ];

        $this->table('role_permissions')->insert($data)->saveData();
    }
}
