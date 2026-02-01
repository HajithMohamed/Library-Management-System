# University Library Management System

A comprehensive Library Management System built with PHP using MVC architecture and Docker containerization.

## Features

- **User Management**: Student, Faculty, and Admin user types
- **Role-Based Access Control (RBAC)**: Granular permission system with 6 predefined roles and 37 permissions
- **Book Management**: Add, edit, delete, and search books
- **Borrowing System**: Borrow and return books with fine calculation
- **Admin Dashboard**: Comprehensive admin panel with reports and statistics
- **Email Notifications**: OTP verification and notifications via PHPMailer
- **Responsive Design**: Modern Bootstrap-based UI
- **Docker Support**: Complete containerization with Nginx, PHP-FPM, MySQL, and PHPMyAdmin

## Project Structure

```
├── src/                    # Application source code
│   ├── controllers/        # Request handling controllers
│   │   ├── AuthController.php
│   │   ├── BookController.php
│   │   ├── UserController.php
│   │   └── AdminController.php
│   ├── models/            # Database access models
│   │   ├── Book.php
│   │   ├── User.php
│   │   └── Transaction.php
│   ├── services/          # Business logic services
│   │   ├── AuthService.php
│   │   ├── BookService.php
│   │   ├── UserService.php
│   │   └── AdminService.php
│   ├── helpers/           # Utility functions
│   │   └── AuthHelper.php
│   ├── config/            # Configuration files
│   │   ├── config.php
│   │   └── dbConnection.php
│   ├── views/             # View templates
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── books/
│   │   ├── users/
│   │   ├── admin/
│   │   └── errors/
│   └── public/            # Public entry point and assets
│       ├── index.php      # Front Controller
│       └── assets/        # CSS, JS, images
├── tests/                 # Test files (organized by type)
│   ├── Unit/              # Unit tests for models and components
│   ├── Integration/       # Integration tests for database and APIs
│   └── Feature/           # Feature/workflow tests
├── storage/               # Application storage
│   ├── cache/             # Cache files
│   ├── uploads/           # User uploaded files
│   └── sessions/          # PHP session files
├── config/                # Root-level configuration
│   └── credentials.example.php  # Credentials template
├── logs/                  # Application logs
├── docker/                # Docker configuration files
├── .env                   # Environment variables (not in git)
├── .env.example           # Environment template
└── SECURITY.md            # Security policy and best practices
```


## Quick Start with Docker

### Prerequisites

Before deploying, ensure you have the following installed on your local system:

- **Docker** and **Docker Compose**
- **PHP 8.0+** (for running Composer locally)
- **Composer** (PHP dependency manager)

### Deployment Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd lms
   ```

2. **Install Composer dependencies locally**
   
   This step is **required** before running Docker. Run this command in your project root:
   
   ```bash
   composer install
   ```
   
   This will create the `vendor/` folder with all necessary PHP dependencies in your local project directory.

3. **Start the application with Docker**
   
   Once Composer dependencies are installed, run:
   
   ```bash
   docker-compose up -d --build
   ```
   
   The Docker build process will:
   - Copy your local `vendor/` folder to the PHP container
   - Set up all necessary services (Nginx, PHP-FPM, MySQL, PHPMyAdmin)
   - Initialize the database with the schema

4. **Access the application**
   - Main Application: http://localhost:8080
   - PHPMyAdmin: http://localhost:8081

5. **Default Admin Credentials**
   - User ID: `admin`
   - Password: `admin123`
   - Admin Code: `hello_world`

### Dependency Management

The application uses Docker volumes to handle the `vendor/` folder:

```yaml
# docker-compose.yml volume configuration
volumes:
  - ./vendor:/var/www/html/vendor  # Mounts local vendor to container
```

This approach ensures:
- ✅ Dependencies are installed locally and then copied to the container
- ✅ No need to rebuild the Docker image when dependencies change
- ✅ Faster Docker builds
- ✅ Consistent development and production environments
- ✅ Easy troubleshooting with local vendor folder visibility

## Docker Services

- **Nginx**: Web server (Port 8080)
- **PHP-FPM**: PHP application server
- **MySQL**: Database server (Port 3307)
- **PHPMyAdmin**: Database management (Port 8081)

## Environment Configuration

The application uses environment variables defined in `.env`:

```env
WEB_PORT=8080
TZ=Asia/Kolkata
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=integrated_library_system
MYSQL_USER=library_user
MYSQL_PASSWORD=library_password
PHPMYADMIN_PORT=8081
DB_HOST=db
DB_PORT=3306
DB_USER=library_user
DB_PASSWORD=library_password
DB_NAME=integrated_library_system
ADMIN_CODE=hello_world
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=noreply@university-library.com
SMTP_FROM_NAME=University Library
```

Copy `.env.example` to `.env` and update with your configuration:

```bash
cp env.example .env
```

## Database Schema

The system includes the following main tables:
- `users`: User accounts and profiles
- `books`: Book inventory
- `transactions`: Borrowing and return records

The database schema is automatically initialized when the MySQL container starts.

## API Endpoints

### Authentication
- `GET /` - Login page
- `POST /` - Process login
- `GET /signup` - Registration page
- `POST /signup` - Process registration
- `GET /logout` - Logout
- `GET /verify-otp` - OTP verification page
- `POST /verify-otp` - Process OTP verification

### User Routes
- `GET /user/dashboard` - User dashboard
- `GET /user/profile` - User profile
- `POST /user/profile` - Update profile
- `POST /user/change-password` - Change password
- `GET /user/books` - Browse books
- `GET /user/borrow` - Borrow book form
- `POST /user/borrow` - Process borrowing
- `GET /user/return` - Return books
- `POST /user/return` - Process return
- `GET /user/fines` - View fines
- `POST /user/pay-fine` - Process fine payment

### Book Routes (Public)
- `GET /books` - Browse all books
- `GET /books/search` - Search books
- `GET /api/books/search` - Search books (API)
- `GET /api/book/details` - Get book details (API)

### Admin Routes
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/books` - Manage books
- `GET /admin/books/add` - Add book form
- `POST /admin/books/add` - Create book
- `GET /admin/books/edit` - Edit book form
- `POST /admin/books/edit` - Update book
- `POST /admin/books/delete` - Delete book
- `GET /admin/users` - Manage users
- `POST /admin/users/delete` - Delete user
- `GET /admin/reports` - System reports
- `GET /admin/fines` - Manage fines
- `POST /admin/fines` - Update fine settings
- `GET /admin/settings` - System settings
- `POST /admin/settings` - Update settings
- `GET /admin/maintenance` - Maintenance panel
- `POST /admin/backup` - Create database backup

## Development

### Local Development Setup

1. **Install PHP 8.0+ and Composer locally**

2. **Install project dependencies**
   ```bash
   composer install
   ```

3. **Install MySQL 8.0+**

4. **Update database configuration in `src/config/config.php`** (if developing without Docker)

5. **Import database schema**
   ```bash
   mysql -u root -p < docker/mysql/library.sql
   ```

### Code Structure

The application follows MVC pattern with:
- **Controllers**: Handle HTTP requests and responses
- **Models**: Database operations and data validation
- **Services**: Business logic and complex operations
- **Views**: HTML templates with PHP
- **Helpers**: Utility functions and common operations

## Features in Detail

### User Management
- User registration with email verification (OTP)
- Role-based access control (Student, Faculty, Admin)
- Profile management and password changes
- Email notifications for account activities

### Book Management
- Complete CRUD operations for books
- ISBN validation and duplicate checking
- Inventory tracking (available/borrowed counts)
- Advanced search and filtering capabilities

### Borrowing System
- Book borrowing with configurable due dates
- Automatic fine calculation for overdue books
- Return processing with fine updates
- Transaction history tracking

### Admin Features
- Comprehensive dashboard with statistics
- User management and account deletion
- Book inventory management
- System reports and analytics
- Fine management and settings
- Database backup functionality
- System maintenance tools

## Security Features

- Password hashing with PHP's `password_hash()`
- CSRF token protection
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Session management and authentication
- Role-based access control (RBAC)
- Email verification with OTP

## Email Configuration

The system uses PHPMailer for email notifications. Configure SMTP settings in your `.env` file:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=noreply@university-library.com
SMTP_FROM_NAME=University Library
```

### Gmail Setup
1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Use the generated password in `SMTP_PASSWORD`

## Troubleshooting

### Common Issues

1. **Composer Command Not Found**
   ```
   command not found: composer
   ```
   **Solution:**
   - Install Composer locally: https://getcomposer.org/download/
   - Add Composer to your system PATH
   - On macOS/Linux: `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`

2. **Vendor Folder Not Found After Docker Build**
   ```
   Fatal error: Failed opening required '/var/www/html/vendor/autoload.php'
   ```
   **Solution:**
   - Ensure you ran `composer install` locally before `docker-compose up`
   - Check that the vendor folder exists in your project root
   - Verify docker-compose.yml has the volume mount: `- ./vendor:/var/www/html/vendor`
   - Rebuild containers: `docker-compose down && docker-compose up -d --build`

3. **Database Connection Error**
   - Check MySQL container is running: `docker-compose ps`
   - Verify database credentials in `.env` file
   - Ensure database exists: `docker exec ils_db mysql -u library_user -p<password> -e "SHOW DATABASES;"`
   - Check container logs: `docker logs ils_db`

4. **Email Not Working**
   - Verify SMTP settings in `.env` file
   - Check Gmail app password (if using Gmail)
   - Ensure network connectivity
   - Test email configuration: Check application logs for SMTP errors

5. **Permission Errors**
   - Check file permissions in Docker volumes
   - Ensure proper ownership: `chown -R $(id -u):$(id -g) ./src`
   - Restart containers: `docker-compose restart`

6. **Container Build Issues**
   - Clean build: `docker-compose down && docker-compose up -d --build`
   - Remove old images: `docker system prune -a`
   - Check Docker Desktop is running
   - View build logs: `docker-compose logs php`

7. **Port Already in Use**
   ```
   Error response from daemon: Ports are not available
   ```
   **Solution:**
   - Change ports in `.env` file (WEB_PORT, PHPMYADMIN_PORT)
   - Or stop other services using those ports

### Viewing Logs

- Application logs: `docker logs ils_php`
- Database logs: `docker logs ils_db`
- Nginx logs: `docker logs ils_nginx`
- All containers: `docker-compose logs`
- Follow logs in real-time: `docker-compose logs -f php`

### Useful Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View running containers
docker-compose ps

# View all containers (including stopped)
docker-compose ps -a

# Rebuild images
docker-compose up -d --build

# Execute command in container
docker exec ils_php php -v

# Access PHP container shell
docker exec -it ils_php bash

# View container logs
docker logs ils_php

# Clean up unused Docker resources
docker system prune -a
```

## Production Deployment

### Production Deployment Checklist

Before deploying to a production environment, complete the following steps:

#### 1. Environment Configuration
- [ ] Copy `.env.example` to `.env` and configure all variables
- [ ] Set `APP_DEBUG=false` in production `.env`
- [ ] Use strong, unique passwords for all credentials
- [ ] Change `ADMIN_CODE` from default value
- [ ] Configure SMTP with valid credentials and test email delivery
- [ ] Configure Cloudinary for production image hosting (if used)
- [ ] Set correct timezone in `TZ` variable

#### 2. Security Hardening
- [ ] Review and implement all items in `SECURITY.md`
- [ ] Ensure `.env` file is not committed to git
- [ ] Set proper file permissions (see Security Best Practices below)
- [ ] Enable HTTPS with valid SSL/TLS certificate
- [ ] Configure security headers in web server (Nginx/Apache)
- [ ] Disable directory listing in web server
- [ ] Review all user input validation and sanitization
- [ ] Implement rate limiting on authentication endpoints
- [ ] Enable CSRF protection on all forms
- [ ] Review and update CORS policies

#### 3. Database Security
- [ ] Create dedicated database user with minimal privileges
- [ ] Never use root database user for application
- [ ] Enable MySQL SSL connections
- [ ] Set strong database passwords
- [ ] Configure database firewall rules
- [ ] Set up automated database backups
- [ ] Test backup restoration procedure

#### 4. Performance Optimization
- [ ] Enable PHP OpCache in production
- [ ] Configure proper cache headers
- [ ] Optimize database queries and add indexes
- [ ] Enable gzip compression
- [ ] Minify CSS and JavaScript files
- [ ] Optimize images (compress and use WebP where possible)
- [ ] Set up CDN for static assets (optional)

#### 5. Monitoring & Logging
- [ ] Configure application logging to `logs/` directory
- [ ] Set up log rotation to prevent disk space issues
- [ ] Implement error monitoring (e.g., Sentry, Rollbar)
- [ ] Monitor server resources (CPU, memory, disk)
- [ ] Set up uptime monitoring
- [ ] Configure alerts for critical errors
- [ ] Review logs regularly for security incidents

#### 6. Deployment Process
- [ ] Use git tags for version tracking
- [ ] Document deployment procedure
- [ ] Create staging environment for testing
- [ ] Perform security scan before deployment
- [ ] Run all tests before deploying
- [ ] Plan for zero-downtime deployment if possible
- [ ] Have rollback plan ready

### Production Environment Variables

Ensure these critical variables are set correctly for production:

```bash
# Required for production
APP_DEBUG=false
MYSQL_ROOT_PASSWORD=<strong-unique-password>
MYSQL_PASSWORD=<strong-unique-password>
DB_PASSWORD=<strong-unique-password>
ADMIN_CODE=<strong-unique-code>
SMTP_USERNAME=<your-production-email>
SMTP_PASSWORD=<your-app-password>
CLOUDINARY_CLOUD_NAME=<your-cloud-name>
CLOUDINARY_API_KEY=<your-api-key>
CLOUDINARY_API_SECRET=<your-api-secret>
```

## Security Best Practices

### File Permissions

Set appropriate file permissions to prevent unauthorized access:

```bash
# Navigate to project directory
cd /path/to/Integrated-Library-System

# Set ownership (adjust user:group as needed)
chown -R www-data:www-data .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Restrict sensitive files
chmod 600 .env
chmod 600 config/credentials.php

# Make storage directories writable
chmod 775 storage/cache storage/uploads storage/sessions logs
```

### Credential Management

1. **Never commit sensitive data to git**
   - Use `.env` for environment-specific configuration
   - Use `config/credentials.php` for sensitive credentials
   - Both files are in `.gitignore`

2. **Use strong passwords**
   - Minimum 16 characters
   - Mix of uppercase, lowercase, numbers, and symbols
   - Use a password manager to generate and store passwords

3. **Rotate credentials regularly**
   - Change passwords every 90 days
   - Update API keys when team members leave
   - Revoke unused API keys immediately

4. **Separate credentials by environment**
   - Development, staging, and production should have different credentials
   - Never use production credentials in development

### HTTPS Configuration

Always use HTTPS in production:

1. **Obtain SSL Certificate**
   - Use Let's Encrypt for free SSL certificates
   - Configure auto-renewal

2. **Force HTTPS**
   - Redirect all HTTP traffic to HTTPS
   - Set HSTS (HTTP Strict Transport Security) header

3. **Configure Nginx for HTTPS** (example):
   ```nginx
   server {
       listen 443 ssl http2;
       server_name yourdomain.com;
       
       ssl_certificate /path/to/cert.pem;
       ssl_certificate_key /path/to/key.pem;
       
       # Security headers
       add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
       add_header X-Frame-Options "SAMEORIGIN" always;
       add_header X-Content-Type-Options "nosniff" always;
       add_header X-XSS-Protection "1; mode=block" always;
       
       # Your application configuration
       root /var/www/html/public;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass php:9000;
           fastcgi_index index.php;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
   }
   
   # Redirect HTTP to HTTPS
   server {
       listen 80;
       server_name yourdomain.com;
       return 301 https://$server_name$request_uri;
   }
   ```

### Regular Updates

- Keep PHP, MySQL, and all dependencies up to date
- Monitor security advisories for used packages
- Test updates in staging before applying to production
- Subscribe to security mailing lists for PHP and MySQL

### Backup Strategy

1. **Database Backups**
   - Automated daily backups
   - Keep backups for at least 30 days
   - Store backups off-site (different server/cloud storage)
   - Encrypt backup files

2. **File Backups**
   - Backup user uploads regularly
   - Backup application code (use git)
   - Test restoration procedures monthly

3. **Backup Script Example**:
   ```bash
   #!/bin/bash
   # Daily backup script
   BACKUP_DIR="/backups/$(date +%Y-%m-%d)"
   mkdir -p "$BACKUP_DIR"
   
   # Database backup
   docker exec ils_db mysqldump -u root -p$MYSQL_ROOT_PASSWORD integrated_library_system > "$BACKUP_DIR/database.sql"
   
   # File backup
   tar -czf "$BACKUP_DIR/uploads.tar.gz" storage/uploads/
   
   # Keep only last 30 days
   find /backups -type d -mtime +30 -exec rm -rf {} \;
   ```

## Contributing

We welcome contributions to the Library Management System! Please follow these guidelines:

### Code Style Standards

1. **PHP Code Style**
   - Follow PSR-12 coding standard
   - Use meaningful variable and function names
   - Add PHPDoc comments for all classes and methods
   - Keep functions small and focused (single responsibility)

2. **Indentation & Formatting**
   - Use 2 spaces for indentation (not tabs)
   - Maximum line length: 120 characters
   - Always use braces `{}` for control structures

3. **Naming Conventions**
   - Classes: `PascalCase` (e.g., `UserController`)
   - Methods: `camelCase` (e.g., `getUserById`)
   - Variables: `camelCase` (e.g., `$userId`)
   - Constants: `UPPER_SNAKE_CASE` (e.g., `DB_HOST`)
   - Database tables: `snake_case` (e.g., `borrow_records`)

### Testing Requirements

1. **Write Tests**
   - Add unit tests for new models in `tests/Unit/`
   - Add integration tests for API endpoints in `tests/Integration/`
   - Add feature tests for user workflows in `tests/Feature/`

2. **Test Coverage**
   - Aim for at least 70% code coverage
   - All business logic must be tested
   - Test both success and failure cases

3. **Running Tests**
   ```bash
   # Run all tests
   php tests/Unit/test_user_model.php
   php tests/Integration/testConnection.php
   php tests/Feature/check_history.php
   ```

### Pull Request Process

1. **Before Submitting**
   - Ensure your code follows the style guidelines
   - Write or update tests as needed
   - Update documentation if you changed functionality
   - Test your changes locally with Docker
   - Ensure no linting errors

2. **Creating a Pull Request**
   - Fork the repository
   - Create a feature branch: `git checkout -b feature/your-feature-name`
   - Make your changes in small, logical commits
   - Push to your fork: `git push origin feature/your-feature-name`
   - Open a pull request with a clear description

3. **Pull Request Description Should Include**
   - Summary of changes
   - Motivation and context
   - Related issue numbers (if applicable)
   - Screenshots for UI changes
   - Testing performed

4. **Pull Request Review**
   - At least one approval required
   - All CI checks must pass
   - No merge conflicts
   - Code review feedback must be addressed

### Commit Message Format

Use conventional commit format:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(auth): add two-factor authentication

Implemented TOTP-based 2FA for enhanced security.
Users can enable 2FA in their profile settings.

Closes #123

---

fix(book): resolve issue with ISBN validation

Fixed regex pattern to properly validate ISBN-13 format.

Fixes #456

---

docs(readme): update deployment instructions

Added production deployment checklist and security best practices.
```

### Branch Naming Conventions

- Feature branches: `feature/short-description`
- Bug fixes: `fix/short-description`
- Hotfixes: `hotfix/short-description`
- Documentation: `docs/short-description`

Examples:
- `feature/add-email-notifications`
- `fix/book-search-pagination`
- `hotfix/sql-injection-vulnerability`
- `docs/update-api-documentation`

### Code Review Guidelines

**For Authors:**
- Keep pull requests small and focused
- Respond to feedback promptly
- Don't take criticism personally

**For Reviewers:**
- Be constructive and respectful
- Focus on code quality, not personal preferences
- Approve only when you're confident in the changes
- Check for security vulnerabilities

### Getting Help

- Review existing issues and pull requests
- Check documentation in `README.md` and `SECURITY.md`
- Ask questions in pull request comments
- Contact the development team

## Documentation

### Core Documentation
- **[RBAC Guide](RBAC_GUIDE.md)**: Comprehensive guide to the Role-Based Access Control system
- **[Route Protection Examples](ROUTE_PROTECTION_EXAMPLES.md)**: Examples of migrating from legacy userType to RBAC
- **[Security Policy](SECURITY.md)**: Security best practices and vulnerability reporting
- **[Admin Features](ADMIN_FEATURES_README.md)**: Admin panel features and usage

### RBAC System Overview

The system implements a comprehensive RBAC with:
- **6 Predefined Roles**: Super Admin, Admin, Librarian, Faculty, Student, Guest
- **37 Granular Permissions**: Organized across 6 modules (users, books, transactions, fines, reports, settings, audit)
- **Permission-Based Access**: Granular control over who can perform specific actions
- **Backward Compatible**: Maintains support for legacy `userType` checks during transition

For detailed RBAC usage, see [RBAC_GUIDE.md](RBAC_GUIDE.md).

## License

This project is licensed under the MIT License.

## Support

For support and questions, please:
- Create an issue in the repository for bugs or feature requests
- Review `SECURITY.md` for security-related concerns
- Contact the development team for general inquiries

