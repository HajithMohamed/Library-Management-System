# Route Protection Examples - RBAC Migration Guide

This document provides examples of how to migrate from legacy `userType` checks to the new RBAC permission-based system.

## Table of Contents
- [Basic Permission Checks](#basic-permission-checks)
- [Role-Based Checks](#role-based-checks)
- [Multiple Permission Checks](#multiple-permission-checks)
- [View-Level Protection](#view-level-protection)
- [API Endpoint Protection](#api-endpoint-protection)
- [Migration Examples](#migration-examples)

---

## Basic Permission Checks

### Example 1: Book Creation
**Before (Legacy):**
```php
// In controller or route handler
if ($_SESSION['userType'] !== 'Admin' && $_SESSION['userType'] !== 'Librarian') {
    http_response_code(403);
    die('Access Denied');
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

// In controller or route handler
PermissionMiddleware::requirePermission('books.create');
// Code continues only if user has permission
```

### Example 2: User Management
**Before (Legacy):**
```php
if ($_SESSION['userType'] !== 'Admin') {
    header('Location: /access-denied');
    exit;
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

// Option 1: Using permission
PermissionMiddleware::requirePermission('users.update');

// Option 2: Using role (if permission is too granular)
PermissionMiddleware::requireRole('admin');
```

---

## Role-Based Checks

### Example 3: Admin Dashboard Access
**Before (Legacy):**
```php
$userType = $_SESSION['userType'] ?? '';
if (strtolower($userType) !== 'admin') {
    http_response_code(403);
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: /login');
    exit;
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

PermissionMiddleware::requireRole(['admin', 'librarian']);
// Allows both admins and librarians
```

### Example 4: Multiple Role Check
**Before (Legacy):**
```php
$allowedTypes = ['Admin', 'Librarian', 'Faculty'];
if (!in_array($_SESSION['userType'], $allowedTypes)) {
    die('Access Denied');
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

PermissionMiddleware::requireRole(['admin', 'librarian', 'faculty']);
```

---

## Multiple Permission Checks

### Example 5: Transaction Approval
**Before (Legacy):**
```php
if ($_SESSION['userType'] !== 'Admin' && $_SESSION['userType'] !== 'Librarian') {
    return ['error' => 'Insufficient permissions'];
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

if (!PermissionMiddleware::checkPermission($_SESSION['user_id'], 'transactions.approve')) {
    return ['error' => 'Insufficient permissions'];
}
```

### Example 6: Complex Permission Logic
**Before (Legacy):**
```php
$canApprove = ($_SESSION['userType'] === 'Admin');
$canModify = ($_SESSION['userType'] === 'Admin' || $_SESSION['userType'] === 'Librarian');

if ($action === 'approve' && !$canApprove) {
    die('Cannot approve');
}
if ($action === 'modify' && !$canModify) {
    die('Cannot modify');
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

if ($action === 'approve') {
    PermissionMiddleware::requirePermission('transactions.approve');
}
if ($action === 'modify') {
    PermissionMiddleware::requirePermission('transactions.update');
}
```

---

## View-Level Protection

### Example 7: Conditional UI Elements
**Before (Legacy):**
```php
<!-- In a view file -->
<?php if ($_SESSION['userType'] === 'Admin'): ?>
    <button onclick="deleteUser()">Delete User</button>
<?php endif; ?>
```

**After (RBAC):**
```php
<?php
use App\Middleware\PermissionMiddleware;
$userId = $_SESSION['user_id'] ?? null;
?>

<!-- In a view file -->
<?php if ($userId && PermissionMiddleware::checkPermission($userId, 'users.delete')): ?>
    <button onclick="deleteUser()">Delete User</button>
<?php endif; ?>
```

### Example 8: Navigation Menu
**Before (Legacy):**
```php
<?php if ($_SESSION['userType'] === 'Admin'): ?>
    <li><a href="/admin/users">Manage Users</a></li>
    <li><a href="/admin/reports">Reports</a></li>
<?php endif; ?>
```

**After (RBAC):**
```php
<?php
use App\Middleware\PermissionMiddleware;
$userId = $_SESSION['user_id'] ?? null;
?>

<?php if ($userId && PermissionMiddleware::checkPermission($userId, 'users.read')): ?>
    <li><a href="/admin/users">Manage Users</a></li>
<?php endif; ?>

<?php if ($userId && PermissionMiddleware::checkPermission($userId, 'reports.view')): ?>
    <li><a href="/admin/reports">Reports</a></li>
<?php endif; ?>
```

---

## API Endpoint Protection

### Example 9: RESTful API
**Before (Legacy):**
```php
// API endpoint
public function deleteBook($bookId)
{
    if ($_SESSION['userType'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        return;
    }
    
    // Delete logic
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

public function deleteBook($bookId)
{
    if (!PermissionMiddleware::checkPermission($_SESSION['user_id'], 'books.delete')) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: Missing books.delete permission']);
        return;
    }
    
    // Delete logic
}
```

### Example 10: JSON API with Multiple Actions
**Before (Legacy):**
```php
public function handleBookAction()
{
    $action = $_POST['action'];
    
    $permissions = [
        'create' => ['Admin', 'Librarian'],
        'update' => ['Admin', 'Librarian'],
        'delete' => ['Admin']
    ];
    
    $userType = $_SESSION['userType'];
    if (!in_array($userType, $permissions[$action] ?? [])) {
        return json_encode(['error' => 'Access denied']);
    }
    
    // Action logic
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

public function handleBookAction()
{
    $action = $_POST['action'];
    $userId = $_SESSION['user_id'];
    
    $permissionMap = [
        'create' => 'books.create',
        'update' => 'books.update',
        'delete' => 'books.delete'
    ];
    
    $requiredPermission = $permissionMap[$action] ?? null;
    if (!$requiredPermission || !PermissionMiddleware::checkPermission($userId, $requiredPermission)) {
        return json_encode(['error' => 'Access denied', 'required_permission' => $requiredPermission]);
    }
    
    // Action logic
}
```

---

## Migration Examples

### Example 11: Full Controller Migration
**Before (Legacy):**
```php
class BookController
{
    public function create()
    {
        if ($_SESSION['userType'] !== 'Admin' && $_SESSION['userType'] !== 'Librarian') {
            die('Access Denied');
        }
        // Create book logic
    }
    
    public function delete($id)
    {
        if ($_SESSION['userType'] !== 'Admin') {
            die('Access Denied');
        }
        // Delete book logic
    }
    
    public function view($id)
    {
        // Anyone can view
        // View book logic
    }
}
```

**After (RBAC):**
```php
use App\Middleware\PermissionMiddleware;

class BookController
{
    public function create()
    {
        PermissionMiddleware::requirePermission('books.create');
        // Create book logic
    }
    
    public function delete($id)
    {
        PermissionMiddleware::requirePermission('books.delete');
        // Delete book logic
    }
    
    public function view($id)
    {
        // Books are public, or check read permission if needed
        PermissionMiddleware::requirePermission('books.read');
        // View book logic
    }
}
```

### Example 12: Gradual Migration (Hybrid Approach)
During transition, you can support both systems:

```php
use App\Middleware\PermissionMiddleware;

public function updateBook($id)
{
    $userId = $_SESSION['user_id'] ?? null;
    
    // Try RBAC first, fallback to legacy
    $hasPermission = false;
    
    if ($userId) {
        // New RBAC system
        $hasPermission = PermissionMiddleware::checkPermission($userId, 'books.update');
    }
    
    if (!$hasPermission) {
        // Legacy fallback
        $userType = $_SESSION['userType'] ?? '';
        $hasPermission = in_array($userType, ['Admin', 'Librarian']);
    }
    
    if (!$hasPermission) {
        http_response_code(403);
        die('Access Denied');
    }
    
    // Update book logic
}
```

---

## Permission Naming Convention

Follow the module.action pattern:

- **Module**: The resource type (users, books, transactions, reports, settings, etc.)
- **Action**: The operation (create, read, update, delete, approve, export, etc.)

Examples:
- `users.create` - Create new users
- `books.read` - View books
- `transactions.approve` - Approve transactions
- `reports.export` - Export reports
- `settings.manage` - Manage settings

---

## Quick Reference Table

| Legacy Check | RBAC Equivalent | Use Case |
|--------------|----------------|----------|
| `$_SESSION['userType'] === 'Admin'` | `PermissionMiddleware::checkRole($userId, 'admin')` | Admin-only access |
| `in_array($userType, ['Admin', 'Librarian'])` | `PermissionMiddleware::hasAnyRole($userId, ['admin', 'librarian'])` | Multiple roles |
| Custom logic for create/update/delete | `PermissionMiddleware::checkPermission($userId, 'module.action')` | Specific actions |
| Hard-coded permissions in views | `PermissionMiddleware::checkPermission($userId, 'permission')` | Conditional UI |

---

## Best Practices

1. **Use Permissions for Actions**: Check specific permissions for actions (create, update, delete)
2. **Use Roles for Pages**: Check roles for entire page/section access
3. **Cache Permission Checks**: Store results in variables if checking multiple times
4. **Log Access Denials**: Use audit logging for security monitoring
5. **Provide Clear Error Messages**: Include required permission in error messages
6. **Test Thoroughly**: Test each role's access to each protected resource

---

## Testing Checklist

After migration, verify:

- [ ] All protected routes require appropriate permissions
- [ ] Super Admin can access everything
- [ ] Admin can manage users and books
- [ ] Librarian can manage books but not users
- [ ] Faculty can borrow and renew books
- [ ] Student can only borrow books
- [ ] Guest can only view books
- [ ] 403 errors are logged for unauthorized access attempts

---

**Need Help?**
- See `RBAC_GUIDE.md` for detailed RBAC documentation
- Review `src/Middleware/PermissionMiddleware.php` for available methods
- Check `database/migrations/20260201180000_create_rbac_tables.php` for permission list
