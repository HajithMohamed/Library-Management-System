# Database Migration & Seeding System

This project uses **Phinx** for database migrations and seeding. This system ensures consistent database schemas across all environments (Development, Staging, Production).

## CLI Commands

We provide two main wrapper scripts for ease of use:

### 1. Migrations (`migrate.php`)

Used to manage database schema changes.

```bash
# Check current migration status
php migrate.php status

# Run all pending migrations
php migrate.php up

# Rollback the last migration
php migrate.php down

# Specify environment (default: development)
php migrate.php up --env=testing

# Create a new migration file
php migrate.php create NewFeatureTable
```

### 2. Seeding (`seed.php`)

Used to populate the database with test or default data.

```bash
# Run all seeders
php seed.php

# Run a specific seeder
php seed.php --class=AdminSeeder

# Specify environment
php seed.php --env=testing

# Skip confirmation prompts
php seed.php --force
```

## Available Seeders

- `AdminSeeder`: Secure admin accounts
- `SampleBooksSeeder`: 50+ diverse books
- `TestUsersSeeder`: Student, Faculty, and Librarian test accounts
- `FineSettingsSeeder`: Default fine configurations
- `SystemSettingsSeeder`: Core library settings
- `RolePermissionsSeeder`: Default permission matrix
- `LibraryHoursSeeder`: Standard opening times

## Creating New Migrations

1. Run `php migrate.php create YourMigrationName`
2. Edit the generated file in `database/migrations/`
3. Implement the `change()` method using Phinx's fluent API
4. Test with `php migrate.php up --env=testing`

## Configuration

Settings are located in `phinx.php` and use credentials from your `.env` file.

> [!IMPORTANT]
> Always backup your database before running migrations in production.
