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

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd lms
   ```

2. **Start the application**
   ```bash
   docker-compose up -d --build
   ```

3. **Install Composer dependencies (if needed)**
   If you encounter autoloader errors, manually install dependencies:
   ```bash
   # Check container name
   docker-compose ps
   
   # Copy composer files to container (replace 'ils_php' with your container name)
   docker cp composer.json ils_php:/var/www/html/
   docker cp composer.lock ils_php:/var/www/html/
   
   # Install dependencies
   docker exec ils_php composer install
   ```

4. **Access the application**
   - Main Application: http://localhost:8080
   - PHPMyAdmin: http://localhost:8081

5. **Default Admin Credentials**
   - User ID: `admin`
   - Password: `admin123`
   - Admin Code: `hello_world`

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
```

## Database Schema

The system includes the following main tables:
- `users`: User accounts and profiles
- `books`: Book inventory
- `transactions`: Borrowing and return records

## API Endpoints

### Authentication
- `GET /` - Login page
- `POST /` - Process login
- `GET /signup` - Registration page
- `POST /signup` - Process registration
- `GET /logout` - Logout

### User Routes
- `GET /user/dashboard` - User dashboard
- `GET /user/books` - Browse books
- `GET /user/borrow` - Borrow book form
- `POST /user/borrow` - Process borrowing
- `GET /user/return` - Return books
- `GET /user/fines` - View fines

### Admin Routes
- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/books` - Manage books
- `GET /admin/users` - Manage users
- `GET /admin/reports` - System reports

## Development

### Local Development Setup

1. **Install PHP 8.0+ and Composer**
2. **Install MySQL 8.0+**
3. **Update database configuration in `src/config/config.php`**
4. **Run Composer install**
   ```bash
   composer install
   ```
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
- User registration with email verification
- Role-based access control (Student, Faculty, Admin)
- Profile management and password changes

### Book Management
- Complete CRUD operations for books
- ISBN validation and duplicate checking
- Inventory tracking (available/borrowed counts)

### Borrowing System
- Book borrowing with due date calculation
- Automatic fine calculation for overdue books
- Return processing with fine updates

### Admin Features
- Comprehensive dashboard with statistics
- User management and account deletion
- Book inventory management
- System reports and analytics
- Fine management and updates

## Security Features

- Password hashing with PHP's `password_hash()`
- CSRF token protection
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Session management and authentication

## Email Configuration

The system uses PHPMailer for email notifications. Configure SMTP settings in `src/config/config.php`:

```php
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_PORT", 587);
define("SMTP_USERNAME", "your-email@gmail.com");
define("SMTP_PASSWORD", "your-app-password");
```

## Troubleshooting

### Common Issues

1. **Composer Autoloader Error**
   ```
   Fatal error: Failed opening required '/var/www/html/../vendor/autoload.php'
   ```
   **Solution:**
   ```bash
   # Copy composer files to container
   docker cp composer.json ils_php:/var/www/html/
   docker cp composer.lock ils_php:/var/www/html/
   
   # Install dependencies
   docker exec ils_php composer install
   
   # Verify installation
   docker exec ils_php ls -la /var/www/html/vendor/autoload.php
   ```

2. **Database Connection Error**
   - Check MySQL container is running: `docker-compose ps`
   - Verify database credentials in `.env`
   - Ensure database exists
   - Check container logs: `docker logs ils_db`

3. **Email Not Working**
   - Verify SMTP settings in `src/config/config.php`
   - Check Gmail app password (if using Gmail)
   - Ensure network connectivity
   - Test email configuration

4. **Permission Errors**
   - Check file permissions in Docker volumes
   - Ensure proper ownership of files
   - Restart containers: `docker-compose restart`

5. **Container Build Issues**
   - Clean build: `docker-compose down && docker-compose up -d --build`
   - Remove old images: `docker system prune -a`
   - Check Docker Desktop is running

### Logs

- Application logs: `docker logs ils_php`
- Database logs: `docker logs ils_db`
- Nginx logs: `docker logs ils_nginx`
- All containers: `docker-compose logs`

### Docker Build Process

The Dockerfile automatically installs Composer dependencies during the build process:

```dockerfile
# Copy composer files to the correct location
COPY composer.json composer.lock /var/www/html/

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader
```

If the build process fails to install dependencies, you can manually install them using the troubleshooting steps above.

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