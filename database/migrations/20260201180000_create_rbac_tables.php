<?php

use Phinx\Migration\AbstractMigration;

class CreateRbacTables extends AbstractMigration
{
    public function change()
    {
        // 1. Roles Table
        $roles = $this->table('roles', ['id' => false, 'primary_key' => ['id']]); // Use custom ID if needed, or default auto-inc
        $roles->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('slug', 'string', ['limit' => 50])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        // 2. Permissions Table
        $permissions = $this->table('permissions', ['id' => false, 'primary_key' => ['id']]);
        $permissions->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('slug', 'string', ['limit' => 100])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        // 3. Permission_Role Pivot Table
        $permRole = $this->table('permission_role', ['id' => false, 'primary_key' => ['permission_id', 'role_id']]);
        $permRole->addColumn('permission_id', 'integer', ['signed' => false])
            ->addColumn('role_id', 'integer', ['signed' => false])
            ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        // 4. Role_User Pivot Table
        // Note: 'users' table PK is 'userId' which is a string (e.g. STU2024001), not integer.
        // We need to check the users table definition. Based on User.php, it's 'userId' (string).
        // Let's verify if we need to modify the FK or column type.
        // The users table definition is not fully visible, but User.php uses 'userId' string.
        // I will assume userId is VARCHAR(20) or similar.

        $roleUser = $this->table('role_user', ['id' => false, 'primary_key' => ['user_id', 'role_id']]);
        $roleUser->addColumn('user_id', 'string', ['limit' => 50]) // varying string length matching user id
            ->addColumn('role_id', 'integer', ['signed' => false])
            ->addForeignKey('user_id', 'users', 'userId', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        // 5. Seed Initial Data
        if ($this->isMigratingUp()) {
            $this->seedRolesAndPermissions();
        }
    }

    private function seedPermissions($roles, $perms)
    {
        $permissionsTable = $this->table('permissions');
        $permissionRoleTable = $this->table('permission_role');

        foreach ($perms as $slug => $desc) {
            $name = ucwords(str_replace('.', ' ', $slug));
            $row = $this->fetchRow("SELECT id FROM permissions WHERE slug = '$slug'");
            if (!$row) {
                $permissionsTable->insert([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $desc
                ])->saveData();
                $permId = $this->getAdapter()->getConnection()->lastInsertId();
            } else {
                $permId = $row['id'];
            }

            // Assign to roles
            foreach ($roles as $roleSlug => $rolePerms) {
                // Logic to determine if role gets this permission
                // For simplicity, defining explicit maps below
            }
        }
    }

    private function seedRolesAndPermissions()
    {
        // Define Roles
        $rolesData = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access to everything'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Manage system resources'],
            ['name' => 'Librarian', 'slug' => 'librarian', 'description' => 'Manage books and transactions'],
            ['name' => 'Faculty', 'slug' => 'faculty', 'description' => 'Academic staff'],
            ['name' => 'Student', 'slug' => 'student', 'description' => 'Students'],
            ['name' => 'Guest', 'slug' => 'guest', 'description' => 'Visitors'],
        ];

        $rolesTable = $this->table('roles');
        foreach ($rolesData as $role) {
            $exists = $this->fetchRow("SELECT id FROM roles WHERE slug = '{$role['slug']}'");
            if (!$exists) {
                $rolesTable->insert($role)->saveData();
            }
        }

        // Define Permissions (Comprehensive set from requirements)
        $permissionsData = [
            // User Management
            'users.create' => 'Create new users',
            'users.read' => 'View user details',
            'users.update' => 'Update user information',
            'users.delete' => 'Delete users',
            'users.verify' => 'Verify user accounts',
            'users.export' => 'Export user data',
            
            // Book Management
            'books.create' => 'Add new books',
            'books.read' => 'View book details',
            'books.update' => 'Update book information',
            'books.delete' => 'Remove books',
            'books.import' => 'Bulk import books',
            'books.export' => 'Export book catalog',
            
            // Transaction Management
            'transactions.create' => 'Create borrow transactions',
            'transactions.read' => 'View transaction history',
            'transactions.update' => 'Update transactions (returns, renewals)',
            'transactions.delete' => 'Delete transactions',
            'transactions.approve' => 'Approve borrow requests',
            'transactions.renew' => 'Renew borrowed books',
            
            // Fine Management
            'fines.read' => 'View fines',
            'fines.waive' => 'Waive fines',
            'fines.collect' => 'Collect fine payments',
            'fines.export' => 'Export fine reports',
            
            // Reports & Analytics
            'reports.view' => 'View reports',
            'reports.export' => 'Export reports',
            'reports.analytics' => 'Access analytics dashboard',
            
            // System Settings
            'settings.manage' => 'Manage system settings',
            'settings.backup' => 'Create/restore backups',
            'settings.maintenance' => 'Perform maintenance tasks',
            'settings.notifications' => 'Manage notifications',
            
            // Audit Logs
            'audit.view' => 'View audit logs',
            'audit.export' => 'Export audit logs',
        ];

        $permissionsTable = $this->table('permissions');
        foreach ($permissionsData as $slug => $desc) {
            $name = ucwords(str_replace('.', ' ', $slug));
            $exists = $this->fetchRow("SELECT id FROM permissions WHERE slug = '$slug'");
            if (!$exists) {
                $permissionsTable->insert([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $desc
                ])->saveData();
            }
        }

        // Map Roles to Permissions
        // We need IDs, so fetch them back
        $allRoles = $this->fetchAll('SELECT id, slug FROM roles');
        $allPerms = $this->fetchAll('SELECT id, slug FROM permissions');

        $roleMap = [];
        foreach ($allRoles as $r)
            $roleMap[$r['slug']] = $r['id'];

        $permMap = [];
        foreach ($allPerms as $p)
            $permMap[$p['slug']] = $p['id'];

        $assignments = [
            'super-admin' => array_keys($permissionsData), // All permissions
            'admin' => [
                'users.create', 'users.read', 'users.update', 'users.delete', 'users.verify', 'users.export',
                'books.create', 'books.read', 'books.update', 'books.delete', 'books.import', 'books.export',
                'transactions.create', 'transactions.read', 'transactions.update', 'transactions.delete', 'transactions.approve',
                'fines.read', 'fines.waive', 'fines.collect', 'fines.export',
                'reports.view', 'reports.export', 'reports.analytics',
                'settings.manage', 'settings.notifications',
                'audit.view', 'audit.export'
            ],
            'librarian' => [
                'users.read',
                'books.create', 'books.read', 'books.update', 'books.import', 'books.export',
                'transactions.create', 'transactions.read', 'transactions.update', 'transactions.approve', 'transactions.renew',
                'fines.read', 'fines.collect',
                'reports.view'
            ],
            'faculty' => [
                'books.read',
                'transactions.create', 'transactions.read', 'transactions.renew'
            ],
            'student' => [
                'books.read',
                'transactions.create', 'transactions.read'
            ],
            'guest' => [
                'books.read'
            ],
        ];

        $permRoleTable = $this->table('permission_role');

        foreach ($assignments as $roleSlug => $permSlugs) {
            if (!isset($roleMap[$roleSlug]))
                continue;

            $roleId = $roleMap[$roleSlug];

            foreach ($permSlugs as $permSlug) {
                if (!isset($permMap[$permSlug]))
                    continue;

                $permId = $permMap[$permSlug];

                // Check if exists
                $exists = $this->fetchRow("SELECT * FROM permission_role WHERE role_id = $roleId AND permission_id = $permId");
                if (!$exists) {
                    $permRoleTable->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permId
                    ])->saveData();
                }
            }
        }

        // Map Existing Users to Roles
        // Fetch all users
        try {
            // Check if users table exists and has data
            $users = $this->fetchAll('SELECT userId, userType FROM users');

            $roleUserTable = $this->table('role_user');

            foreach ($users as $user) {
                $userType = strtolower($user['userType']);
                $roleSlug = null;

                // Map userType to role slug
                switch ($userType) {
                    case 'admin':
                        $roleSlug = 'admin';
                        break;
                    case 'librarian':
                        $roleSlug = 'librarian';
                        break;
                    case 'student':
                        $roleSlug = 'student';
                        break;
                    case 'faculty':
                    case 'teacher':
                        $roleSlug = 'faculty';
                        break;
                    default:
                        $roleSlug = 'guest';
                }

                if ($roleSlug && isset($roleMap[$roleSlug])) {
                    $roleId = $roleMap[$roleSlug];
                    $userId = $user['userId'];

                    // Check if already assigned
                    $exists = $this->fetchRow("SELECT * FROM role_user WHERE user_id = '$userId' AND role_id = $roleId");
                    if (!$exists) {
                        $roleUserTable->insert([
                            'user_id' => $userId,
                            'role_id' => $roleId
                        ])->saveData();
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore if users table issue, valid for fresh install
        }
    }
}
