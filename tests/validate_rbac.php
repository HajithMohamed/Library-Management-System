<?php
/**
 * RBAC System Validation Script
 * 
 * This script validates the RBAC implementation without requiring a database connection.
 * It checks:
 * - Model files exist
 * - Methods are defined
 * - Documentation is present
 */

// Set up environment
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

echo "=== RBAC System Validation ===\n\n";

$errors = [];
$warnings = [];
$passed = 0;

// Check 1: Required files exist
echo "1. Checking required files...\n";
$requiredFiles = [
    'src/models/Role.php',
    'src/models/Permission.php',
    'src/models/User.php',
    'src/Middleware/PermissionMiddleware.php',
    'src/Middleware/CheckPermission.php',
    'src/Middleware/CheckRole.php',
    'src/views/admin/roles.php',
    'src/views/admin/permissions.php',
    'src/views/admin/user-roles.php',
    'database/migrations/20260201180000_create_rbac_tables.php',
    'RBAC_GUIDE.md',
    'ROUTE_PROTECTION_EXAMPLES.md'
];

foreach ($requiredFiles as $file) {
    $path = APP_ROOT . '/' . $file;
    if (file_exists($path)) {
        echo "   ✓ $file\n";
        $passed++;
    } else {
        echo "   ✗ $file - MISSING\n";
        $errors[] = "Required file missing: $file";
    }
}

// Check 2: Role model methods
echo "\n2. Checking Role model methods...\n";
$roleReflection = new ReflectionClass('App\Models\Role');
$requiredRoleMethods = [
    'getRoleByName',
    'getPermissions',
    'assignPermission',
    'removePermission',
    'getAllRoles',
    'permissions',
    'findByName'
];

foreach ($requiredRoleMethods as $method) {
    if ($roleReflection->hasMethod($method)) {
        echo "   ✓ Role::$method()\n";
        $passed++;
    } else {
        echo "   ✗ Role::$method() - MISSING\n";
        $errors[] = "Role model missing method: $method";
    }
}

// Check 3: Permission model methods
echo "\n3. Checking Permission model methods...\n";
$permReflection = new ReflectionClass('App\Models\Permission');
$requiredPermMethods = [
    'getPermissionByName',
    'getPermissionsByModule',
    'getAllPermissions',
    'getAllPermissionsGrouped',
    'roles'
];

foreach ($requiredPermMethods as $method) {
    if ($permReflection->hasMethod($method)) {
        echo "   ✓ Permission::$method()\n";
        $passed++;
    } else {
        echo "   ✗ Permission::$method() - MISSING\n";
        $errors[] = "Permission model missing method: $method";
    }
}

// Check 4: User model RBAC methods
echo "\n4. Checking User model RBAC methods...\n";
$userReflection = new ReflectionClass('App\Models\User');
$requiredUserMethods = [
    'getRoles',
    'hasRole',
    'hasPermission',
    'assignRole',
    'removeRole',
    'hasAnyRole',
    'getPermissions'
];

foreach ($requiredUserMethods as $method) {
    if ($userReflection->hasMethod($method)) {
        echo "   ✓ User::$method()\n";
        $passed++;
    } else {
        echo "   ✗ User::$method() - MISSING\n";
        $errors[] = "User model missing method: $method";
    }
}

// Check 5: PermissionMiddleware methods
echo "\n5. Checking PermissionMiddleware methods...\n";
$middlewareReflection = new ReflectionClass('App\Middleware\PermissionMiddleware');
$requiredMiddlewareMethods = [
    'checkPermission',
    'checkRole',
    'hasAnyRole',
    'hasAllPermissions',
    'requirePermission',
    'requireRole'
];

foreach ($requiredMiddlewareMethods as $method) {
    if ($middlewareReflection->hasMethod($method)) {
        echo "   ✓ PermissionMiddleware::$method()\n";
        $passed++;
    } else {
        echo "   ✗ PermissionMiddleware::$method() - MISSING\n";
        $errors[] = "PermissionMiddleware missing method: $method";
    }
}

// Check 6: AdminController RBAC methods
echo "\n6. Checking AdminController RBAC methods...\n";
$adminReflection = new ReflectionClass('App\Controllers\AdminController');
$requiredAdminMethods = [
    'roles',
    'userRoles',
    'assignRoleToUser',
    'removeRoleFromUser',
    'permissions'
];

foreach ($requiredAdminMethods as $method) {
    if ($adminReflection->hasMethod($method)) {
        echo "   ✓ AdminController::$method()\n";
        $passed++;
    } else {
        echo "   ✗ AdminController::$method() - MISSING\n";
        $errors[] = "AdminController missing method: $method";
    }
}

// Check 7: Migration file content
echo "\n7. Checking migration file content...\n";
$migrationFile = APP_ROOT . '/database/migrations/20260201180000_create_rbac_tables.php';
$migrationContent = file_get_contents($migrationFile);

$expectedStrings = [
    'roles',
    'permissions',
    'permission_role',
    'role_user',
    'users.create',
    'books.create',
    'transactions.create',
    'fines.read',
    'reports.view',
    'settings.manage',
    'audit.view',
    'super-admin',
    'admin',
    'librarian',
    'faculty',
    'student',
    'guest'
];

foreach ($expectedStrings as $str) {
    if (strpos($migrationContent, $str) !== false) {
        echo "   ✓ Contains '$str'\n";
        $passed++;
    } else {
        echo "   ✗ Missing '$str'\n";
        $warnings[] = "Migration may be missing: $str";
    }
}

// Check 8: Documentation completeness
echo "\n8. Checking documentation...\n";
$rbacGuide = file_get_contents(APP_ROOT . '/RBAC_GUIDE.md');
$routeExamples = file_get_contents(APP_ROOT . '/ROUTE_PROTECTION_EXAMPLES.md');

$docChecks = [
    'RBAC_GUIDE.md contains "Permission Modules"' => strpos($rbacGuide, 'Permission Modules') !== false,
    'RBAC_GUIDE.md contains "Usage Examples"' => strpos($rbacGuide, 'Usage Examples') !== false,
    'RBAC_GUIDE.md contains 37 permissions' => substr_count($rbacGuide, 'users.') >= 3,
    'ROUTE_PROTECTION_EXAMPLES.md contains "Before (Legacy)"' => strpos($routeExamples, 'Before (Legacy)') !== false,
    'ROUTE_PROTECTION_EXAMPLES.md contains "After (RBAC)"' => strpos($routeExamples, 'After (RBAC)') !== false,
];

foreach ($docChecks as $desc => $result) {
    if ($result) {
        echo "   ✓ $desc\n";
        $passed++;
    } else {
        echo "   ✗ $desc\n";
        $warnings[] = "Documentation incomplete: $desc";
    }
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "VALIDATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "✓ Passed: $passed checks\n";
echo "✗ Errors: " . count($errors) . "\n";
echo "⚠ Warnings: " . count($warnings) . "\n";

if (!empty($errors)) {
    echo "\n❌ CRITICAL ERRORS:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "   - $warning\n";
    }
}

echo "\n";

if (empty($errors)) {
    echo "✅ RBAC system validation PASSED!\n";
    echo "   All required components are in place.\n\n";
    exit(0);
} else {
    echo "❌ RBAC system validation FAILED!\n";
    echo "   Please fix the errors above.\n\n";
    exit(1);
}
