<?php
// Script to manually create RBAC tables since Phinx is unavailable
// Script to manually create RBAC tables since Phinx is unavailable
// Define basic constants for CLI environment to prevent config defaults from taking over incorrectly if .env is missing
if (!defined('DB_HOST'))
    define('DB_HOST', 'localhost');
if (!defined('DB_USER'))
    define('DB_USER', 'root');
if (!defined('DB_PASSWORD'))
    define('DB_PASSWORD', '');
if (!defined('DB_NAME'))
    define('DB_NAME', 'integrated_library_system');

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/config/dbConnection.php';

echo "Starting manual migration...\n";

try {
    // 1. Create Roles Table
    $mysqli->query("
CREATE TABLE IF NOT EXISTS roles (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
slug VARCHAR(255) NOT NULL UNIQUE,
description TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");
    echo "Created 'roles' table.\n";

    // 2. Create Permissions Table
    $mysqli->query("
CREATE TABLE IF NOT EXISTS permissions (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
slug VARCHAR(255) NOT NULL UNIQUE,
description TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");
    echo "Created 'permissions' table.\n";

    // 3. Create role_user Pivot Table
    $mysqli->query("
CREATE TABLE IF NOT EXISTS role_user (
user_id VARCHAR(50) NOT NULL,
role_id INT NOT NULL,
PRIMARY KEY (user_id, role_id),
FOREIGN KEY (user_id) REFERENCES users(userId) ON DELETE CASCADE,
FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");
    echo "Created 'role_user' table.\n";

    // 4. Create permission_role Pivot Table
    $mysqli->query("
CREATE TABLE IF NOT EXISTS permission_role (
permission_id INT NOT NULL,
role_id INT NOT NULL,
PRIMARY KEY (permission_id, role_id),
FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");
    echo "Created 'permission_role' table.\n";

    // 5. Seed Initial Data
// Roles
    $roles = [
        ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access to everything'],
        ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Manage users, books, and transactions'],
        ['name' => 'Librarian', 'slug' => 'librarian', 'description' => 'Manage books and transactions'],
        ['name' => 'Faculty', 'slug' => 'faculty', 'description' => 'Extended borrowing privileges'],
        ['name' => 'Student', 'slug' => 'student', 'description' => 'Standard borrowing privileges'],
        ['name' => 'Guest', 'slug' => 'guest', 'description' => 'View only access']
    ];

    foreach ($roles as $role) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO roles (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $role['name'], $role['slug'], $role['description']);
        $stmt->execute();
    }
    echo "Seeded roles.\n";

    // Permissions
    $permissions = [
        'users.create',
        'users.read',
        'users.update',
        'users.delete',
        'books.create',
        'books.read',
        'books.update',
        'books.delete',
        'transactions.manage',
        'reports.view',
        'settings.manage',
        '*' // All access
    ];

    foreach ($permissions as $slug) {
        $name = ucwords(str_replace('.', ' ', $slug));
        $stmt = $mysqli->prepare("INSERT IGNORE INTO permissions (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
        $stmt->execute();
    }
    echo "Seeded permissions.\n";

    // 6. Assign Permissions to Roles (Basic mapping)
// Get IDs
    $roleIds = [];
    $res = $mysqli->query("SELECT id, slug FROM roles");
    while ($row = $res->fetch_assoc())
        $roleIds[$row['slug']] = $row['id'];

    $permIds = [];
    $res = $mysqli->query("SELECT id, slug FROM permissions");
    while ($row = $res->fetch_assoc())
        $permIds[$row['slug']] = $row['id'];

    // Super Admin -> *
    $mysqli->query("INSERT IGNORE INTO permission_role (role_id, permission_id) VALUES ({$roleIds['super-admin']},
{$permIds['*']})");

    // Admin -> users.*, books.*, transactions.manage
    if (isset($roleIds['admin'])) {
        foreach ($permIds as $slug => $id) {
            if ($slug !== '*') {
                $mysqli->query("INSERT IGNORE INTO permission_role (role_id, permission_id) VALUES ({$roleIds['admin']}, $id)");
            }
        }
    }

    echo "Assigned permissions to roles.\n";

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}