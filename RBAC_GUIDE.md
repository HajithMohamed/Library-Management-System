# RBAC (Role-Based Access Control) Guide

## Overview

The Library Management System now implements a comprehensive Role-Based Access Control (RBAC) system that provides granular permissions management across the entire application.

## Architecture

### Database Structure

The RBAC system uses four main tables:

1. **`roles`** - Defines system roles
2. **`permissions`** - Defines granular permissions
3. **`permission_role`** - Many-to-many relationship between roles and permissions
4. **`role_user`** - Many-to-many relationship between users and roles

### Key Components

- **Models**:
  - `Role.php` - Role management and permission queries
  - `Permission.php` - Permission management and module organization
  - `User.php` - Extended with RBAC methods

- **Middleware**:
  - `PermissionMiddleware.php` - Permission and role checking utilities
  - `CheckPermission.php` - Permission enforcement middleware
  - `CheckRole.php` - Role enforcement middleware

## Predefined Roles

### 1. Super Admin
- **Slug**: `super-admin`
- **Description**: Full system access
- **Permissions**: All permissions (automatic)

### 2. Admin
- **Slug**: `admin`
- **Description**: User and system management
- **Permissions**:
  - All user management operations
  - All book management operations
  - Transaction management and approval
  - Fine management (view, waive, collect, export)
  - Reports and analytics
  - System settings and notifications
  - Audit log access

### 3. Librarian
- **Slug**: `librarian`
- **Description**: Book and transaction management
- **Permissions**:
  - View users
  - Full book management (create, update, import, export)
  - Transaction management and approval
  - Fine collection
  - View reports

### 4. Faculty
- **Slug**: `faculty`
- **Description**: Extended borrowing privileges
- **Permissions**:
  - View books
  - Create and view transactions
  - Renew borrowed books

### 5. Student
- **Slug**: `student`
- **Description**: Basic borrowing access
- **Permissions**:
  - View books
  - Create and view transactions

### 6. Guest
- **Slug**: `guest`
- **Description**: View-only access
- **Permissions**:
  - View books only

## Permission Modules

Permissions are organized into logical modules:

### User Management (`users.*`)
- `users.create` - Create new users
- `users.read` - View user details
- `users.update` - Update user information
- `users.delete` - Delete users
- `users.verify` - Verify user accounts
- `users.export` - Export user data

### Book Management (`books.*`)
- `books.create` - Add new books
- `books.read` - View book details
- `books.update` - Update book information
- `books.delete` - Remove books
- `books.import` - Bulk import books
- `books.export` - Export book catalog

### Transaction Management (`transactions.*`)
- `transactions.create` - Create borrow transactions
- `transactions.read` - View transaction history
- `transactions.update` - Update transactions (returns, renewals)
- `transactions.delete` - Delete transactions
- `transactions.approve` - Approve borrow requests
- `transactions.renew` - Renew borrowed books

### Fine Management (`fines.*`)
- `fines.read` - View fines
- `fines.waive` - Waive fines
- `fines.collect` - Collect fine payments
- `fines.export` - Export fine reports

### Reports & Analytics (`reports.*`)
- `reports.view` - View reports
- `reports.export` - Export reports
- `reports.analytics` - Access analytics dashboard

### System Settings (`settings.*`)
- `settings.manage` - Manage system settings
- `settings.backup` - Create/restore backups
- `settings.maintenance` - Perform maintenance tasks
- `settings.notifications` - Manage notifications

### Audit Logs (`audit.*`)
- `audit.view` - View audit logs
- `audit.export` - Export audit logs

## Usage Examples

### Checking Permissions in Controllers

```php
use App\Middleware\PermissionMiddleware;

// Check if user has permission
if (!PermissionMiddleware::checkPermission($_SESSION['user_id'], 'books.create')) {
    // User doesn't have permission
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Require permission (auto-exit if denied)
PermissionMiddleware::requirePermission('books.create');
```

### Checking Roles in Controllers

```php
use App\Middleware\PermissionMiddleware;

// Check if user has role
if (!PermissionMiddleware::checkRole($_SESSION['user_id'], 'admin')) {
    // User doesn't have role
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Require role (auto-exit if denied)
PermissionMiddleware::requireRole('admin');

// Check if user has any of multiple roles
if (PermissionMiddleware::hasAnyRole($_SESSION['user_id'], ['admin', 'librarian'])) {
    // User has at least one of these roles
}
```

### Using User Model Methods

```php
$userModel = new \App\Models\User();
$userId = $_SESSION['user_id'];

// Get user's roles
$roles = $userModel->getRoles($userId);

// Check if user has specific role
if ($userModel->hasRole($userId, 'admin')) {
    // User is an admin
}

// Check if user has permission
if ($userModel->hasPermission($userId, 'books.delete')) {
    // User can delete books
}

// Assign role to user
$userModel->assignRole($userId, 'librarian');

// Remove role from user
$userModel->removeRole($userId, 'student');
```

### Route Protection (Before)

**Old approach using userType:**
```php
if ($_SESSION['userType'] !== 'Admin') {
    header('Location: ' . BASE_URL . 'access-denied');
    exit;
}
```

### Route Protection (After - RBAC)

**New approach using permissions:**
```php
use App\Middleware\PermissionMiddleware;

// Check permission
if (!PermissionMiddleware::checkPermission($_SESSION['user_id'], 'books.create')) {
    header('Location: ' . BASE_URL . 'access-denied');
    exit;
}

// Or check role
if (!PermissionMiddleware::checkRole($_SESSION['user_id'], 'admin')) {
    header('Location: ' . BASE_URL . 'access-denied');
    exit;
}
```

## Admin Panel Management

### Managing Roles and Permissions

Access the admin panel to manage the RBAC system:

1. **Roles Management** (`/admin/roles`)
   - View all roles
   - See permissions assigned to each role
   - Edit role permissions (future feature)

2. **Permissions Management** (`/admin/permissions`)
   - View all permissions grouped by module
   - See which roles have which permissions
   - Export permissions as CSV
   - View permission matrix (roles vs permissions)

3. **User Roles Management** (`/admin/user-roles`)
   - View all users and their assigned roles
   - Assign roles to users
   - Remove roles from users
   - Filter by role or user type

## Migration and Seeding

### Running Migrations

The RBAC tables are created automatically via Phinx migrations:

```bash
php vendor/bin/phinx migrate
```

This will:
1. Create the four RBAC tables
2. Seed initial roles (6 roles)
3. Seed permissions (37 permissions)
4. Assign permissions to roles
5. Migrate existing users to roles based on their `userType`

### User Migration Logic

During migration, existing users are automatically assigned roles based on their `userType`:

- `userType: Admin` → `role: admin`
- `userType: Librarian` → `role: librarian`
- `userType: Student` → `role: student`
- `userType: Faculty` or `Teacher` → `role: faculty`
- Other types → `role: guest`

## Backward Compatibility

The `userType` column is maintained for backward compatibility during the transition period. Both systems work simultaneously:

- Old code using `$_SESSION['userType']` continues to work
- New code can use RBAC permission checks
- Gradually migrate all permission checks to RBAC
- Future versions will remove `userType` dependency

## Performance Considerations

### Caching

For optimal performance:

1. **Session Caching**: Cache user permissions in session after login
2. **Database Indexes**: The system includes indexes on foreign keys
3. **Query Optimization**: Use `hasPermission()` sparingly in loops

### Best Practices

- Check permissions at the controller level, not in views
- Cache permission results when checking multiple times
- Use role checks for broad access, permission checks for specific actions
- Leverage super-admin shortcut (automatic all permissions)

## Security Best Practices

1. **Principle of Least Privilege**: Assign minimal permissions required
2. **Regular Audits**: Review user roles and permissions periodically
3. **Audit Logging**: All role/permission changes are logged
4. **Prevent Privilege Escalation**: Users cannot assign roles higher than their own
5. **Validate Input**: Always validate role/permission slugs before assignment

## Testing

### Manual Testing Checklist

- [ ] Admin can access all resources
- [ ] Librarian can manage books but not users
- [ ] Faculty can borrow and renew books
- [ ] Student can only borrow books
- [ ] Guest can only view books
- [ ] Permission denied (403) shown for unauthorized access

### Automated Testing

```php
// Example PHPUnit test
public function testStudentCannotDeleteBooks()
{
    $user = new User();
    $hasPermission = $user->hasPermission('STU2024001', 'books.delete');
    $this->assertFalse($hasPermission);
}
```

## Troubleshooting

### Common Issues

**Issue**: User has no permissions after migration
- **Solution**: Run migration again or manually assign a role

**Issue**: Permission check always returns false
- **Solution**: Verify user has a role assigned and role has the permission

**Issue**: Super admin can't access resources
- **Solution**: Check that `hasRole($userId, 'super-admin')` shortcut is working

## Future Enhancements

1. Dynamic role creation via admin panel
2. Custom permission creation
3. Permission inheritance and hierarchies
4. Time-based permissions (temporary access)
5. Resource-level permissions (e.g., "edit own books only")
6. API for third-party integrations

## API Endpoints

### Assign Role to User
```
POST /admin/users/assign-role
Content-Type: application/json

{
  "userId": "STU2024001",
  "roleSlug": "student"
}
```

### Remove Role from User
```
POST /admin/users/remove-role
Content-Type: application/json

{
  "userId": "STU2024001",
  "roleId": 5
}
```

## Support

For questions or issues with the RBAC system:
- Check this guide
- Review the migration file: `database/migrations/20260201180000_create_rbac_tables.php`
- Examine model implementations: `src/models/Role.php`, `src/models/Permission.php`
- Test with the admin panel at `/admin/roles` and `/admin/user-roles`

---

**Last Updated**: February 2026
**Version**: 1.0.0
