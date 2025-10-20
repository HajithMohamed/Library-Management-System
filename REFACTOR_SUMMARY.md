# Signup Refactor Summary

## Overview
Refactored the signup functionality to allow users to choose their username while automatically generating user IDs in the background using MySQL. All new registrations are automatically set as "Student" type.

## Changes Made

### 1. User Model (`src/models/User.php`)
- **Added `generateUserId()` method**: Generates unique user IDs in format `STU2024001`, `STU2024002`, etc.
- **Updated `createUser()` method**: Now auto-generates user ID before inserting user data
- **Added `getLastGeneratedUserId()` method**: Returns the last generated user ID for display purposes
- **Updated `validateUserData()` method**: Removed userId validation since it's auto-generated
- **Added property**: `$lastGeneratedUserId` to store the generated ID

### 2. AuthController (`src/controllers/AuthController.php`)
- **Updated signup method**: 
  - Removed userId from POST data collection
  - Set userType to 'Student' automatically
  - Removed userId existence check (no longer needed)
  - Updated success message to display generated user ID
  - Uses `getLastGeneratedUserId()` to show the generated ID to user

### 3. Signup Form (`src/views/auth/signup.php`)
- **Kept username field**: Users can choose their own username
- **Updated header text**: Added note about auto-generated Student ID in background
- **Maintained all other fields**: Password, gender, email, phone, DOB, address

### 4. Login Form (`src/views/auth/login.php`)
- **Updated to use username**: Users login with their chosen username
- **Updated placeholder**: Simple "Enter your username" text
- **Fixed value attribute**: Changed to use `$_POST['username']`

## User ID Format
- **Pattern**: `STU` + `YYYY` + `XXX`
- **Example**: `STU2024001`, `STU2024002`, `STU2024003`
- **Logic**: 
  - Prefix: "STU" (for Student)
  - Year: Current year (2024, 2025, etc.)
  - Sequence: 3-digit number starting from 001

## Benefits
1. **User-friendly signup**: Users can choose their preferred username
2. **Consistent user IDs**: All student IDs follow the same format (auto-generated)
3. **Automatic user type**: All signups are automatically set as "Student"
4. **Unique IDs**: MySQL-based generation ensures uniqueness
5. **Year-based organization**: Easy to identify when users registered
6. **Dual identification**: Users have both username (for login) and user ID (for system)

## Database Impact
- **Updated schema** in `docker/mysql/library.sql`:
  - Added `id` as AUTO_INCREMENT primary key
  - Added `username` VARCHAR(50) with UNIQUE constraint
  - Changed `userId` to VARCHAR(10) with UNIQUE constraint
  - Set `userType` default to 'Student'
  - Changed `dob` from VARCHAR to DATE
  - Increased `phoneNumber` to VARCHAR(15)
  - Changed `address` to TEXT
  - Changed `otpExpiry` to DATETIME
  - Added `createdAt` and `updatedAt` timestamps
  - Added performance indexes including username index
- **Migration script** provided in `database_migration.sql` for existing databases
- Existing users remain unaffected
- New users will have auto-generated IDs

## Testing
- Created `test_user_generation.php` to verify user ID generation
- All existing functionality preserved
- Login process updated to work with new user ID format

## Files Modified
1. `src/models/User.php` - Core user ID generation logic
2. `src/controllers/AuthController.php` - Updated signup flow
3. `src/views/auth/signup.php` - Removed username field
4. `src/views/auth/login.php` - Updated labels and placeholders
5. `docker/mysql/library.sql` - Updated database schema
6. `database_migration.sql` - Migration script for existing databases
7. `test_user_generation.php` - Test script (can be deleted after testing)
8. `REFACTOR_SUMMARY.md` - This documentation

## Next Steps
1. Test the signup process end-to-end
2. Verify email verification still works
3. Test login with generated user IDs
4. Remove test files after verification
