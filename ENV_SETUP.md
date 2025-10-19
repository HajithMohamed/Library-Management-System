# Environment Variables Setup

This project uses the `vlucas/phpdotenv` package for environment variable management. Follow these steps to set up your environment:

## 1. Create .env File

Copy the example environment file and customize it:

```bash
cp env.example .env
```

## 2. Configure Your .env File

Edit the `.env` file with your actual values:

### Database Configuration
```env
MYSQL_ROOT_PASSWORD=your_secure_root_password
MYSQL_DATABASE=integrated_library_system
MYSQL_USER=library_user
MYSQL_PASSWORD=your_secure_password
```

### Application Database Settings
```env
DB_HOST=db
DB_PORT=3306
DB_USER=library_user
DB_PASSWORD=your_secure_password
DB_NAME=integrated_library_system
```

### Admin Configuration
```env
ADMIN_CODE=your_admin_registration_code
```

### Email Configuration (SMTP)
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=your_email@gmail.com
SMTP_FROM_NAME=Library Management System University of Ruhuna
```

### SMS Gateway (Optional)
```env
SMS_API_URL=https://api.textlocal.in/send/
SMS_API_KEY=your_sms_api_key
SMS_SENDER_ID=your_sender_id
```

## 3. Gmail App Password Setup

For Gmail SMTP, you need to:

1. Enable 2-Factor Authentication on your Google account
2. Generate an App Password:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a password for "Mail"
   - Use this password in `SMTP_PASSWORD`

## 4. Install Dependencies

Make sure to install the dotenv package:

```bash
composer install
```

## 5. Run the Application

```bash
docker-compose up -d
```

## 6. Security Notes

- Never commit your `.env` file to version control
- Use strong, unique passwords
- The `.env` file is already in `.gitignore`
- Change default admin code for production

## 7. Benefits of Using Dotenv Package

- **Robust parsing**: Handles complex values, quotes, and special characters
- **Type safety**: Proper validation and type conversion
- **Error handling**: Clear error messages for malformed files
- **Performance**: Optimized loading and caching
- **Standards compliance**: Follows the 12-factor app methodology

## 8. Fallback Values

If environment variables are not set, the application will use these default values:
- Database: `db:3306` with user `library_user`
- Admin Code: `hello_world`
- Timezone: `Asia/Kolkata`
- SMTP: Gmail with placeholder values

## 9. Troubleshooting

If you encounter issues:

1. Check that your `.env` file exists in the project root
2. Verify all required variables are set
3. Run `composer install` to ensure dotenv package is installed
4. Restart containers: `docker-compose down && docker-compose up -d`
5. Check container logs: `docker-compose logs php`
