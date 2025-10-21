# University Library Management System

A comprehensive Library Management System built with PHP using MVC architecture and Docker containerization.

## Features

- **User Management**: Student, Faculty, and Admin user types
- **Book Management**: Add, edit, delete, and search books
- **Borrowing System**: Borrow and return books with fine calculation
- **Admin Dashboard**: Comprehensive admin panel with reports and statistics
- **Email Notifications**: OTP verification and notifications via PHPMailer
- **Responsive Design**: Modern Bootstrap-based UI
- **Docker Support**: Complete containerization with Nginx, PHP-FPM, MySQL, and PHPMyAdmin

## Project Structure

```
src/
├── controllers/          # Request handling controllers
│   ├── AuthController.php
│   ├── BookController.php
│   ├── UserController.php
│   └── AdminController.php
├── models/              # Database access models
│   ├── Book.php
│   ├── User.php
│   └── Transaction.php
├── services/            # Business logic services
│   ├── AuthService.php
│   ├── BookService.php
│   ├── UserService.php
│   └── AdminService.php
├── helpers/             # Utility functions
│   └── AuthHelper.php
├── config/              # Configuration files
│   ├── config.php
│   └── dbConnection.php
├── views/               # View templates
│   ├── layouts/
│   ├── auth/
│   ├── books/
│   ├── users/
│   ├── admin/
│   └── errors/
└── public/              # Public entry point and assets
    ├── index.php        # Front Controller
    └── assets/          # CSS, JS, images
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

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team or create an issue in the repository.

