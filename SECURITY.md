# Security Policy

## Reporting a Vulnerability

The Library Management System team takes security vulnerabilities seriously. We appreciate your efforts to responsibly disclose your findings.

### How to Report

**Please DO NOT report security vulnerabilities through public GitHub issues.**

Instead, please report security vulnerabilities by:

1. **Email**: Send details to your security team email address
   - Subject line: `[SECURITY] Brief description of vulnerability`
   - Include detailed steps to reproduce the vulnerability
   - Attach screenshots or proof-of-concept code if applicable

2. **Expected Response Time**:
   - **Initial Response**: Within 48 hours of report submission
   - **Status Update**: Within 7 days with assessment and planned actions
   - **Resolution**: Critical vulnerabilities will be addressed within 30 days

3. **What to Include in Your Report**:
   - Type of vulnerability (e.g., XSS, SQL injection, authentication bypass)
   - Full paths or URLs of affected source files
   - Steps to reproduce the vulnerability
   - Proof-of-concept or exploit code (if possible)
   - Potential impact of the vulnerability
   - Suggested fix (if you have one)

### Security Disclosure Policy

- We will acknowledge receipt of your vulnerability report
- We will provide an estimated timeline for a fix
- We will notify you when the vulnerability is fixed
- We will credit you in our security acknowledgments (if desired)

## Supported Versions

Security updates are provided for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| Latest  | ✅ Yes             |
| < 1.0   | ❌ No              |

**Recommendation**: Always use the latest version for the most up-to-date security patches.

## Known Security Features

This application implements the following security measures:

### Authentication & Authorization
- ✅ Password hashing using PHP `password_hash()` with bcrypt
- ✅ Email verification with OTP (One-Time Password)
- ✅ Role-based access control (Student, Faculty, Admin)
- ✅ Session management with secure session handling
- ✅ Admin code requirement for administrative account creation

### Data Protection
- ✅ SQL injection prevention using prepared statements
- ✅ XSS protection with `htmlspecialchars()` output encoding
- ✅ CSRF token protection (where implemented)
- ✅ Input validation and sanitization
- ✅ Secure database connection with SSL support

### Configuration Security
- ✅ Environment variables for sensitive data (.env)
- ✅ Credentials excluded from version control
- ✅ Debug mode disabled in production
- ✅ Error logging instead of display

### Infrastructure Security
- ✅ Docker containerization for isolation
- ✅ Nginx reverse proxy configuration
- ✅ File upload restrictions and validation
- ✅ Rate limiting on authentication endpoints (recommended to implement)

## Security Best Practices

### For Developers

1. **Never Commit Sensitive Data**
   - Do not commit `.env` files, `config/credentials.php`, or files containing passwords/API keys
   - Review code changes before committing to ensure no secrets are included
   - Use `.gitignore` properly

2. **Input Validation**
   - Always validate and sanitize user input
   - Use prepared statements for all database queries
   - Implement server-side validation (never rely solely on client-side)

3. **Authentication**
   - Use strong password requirements
   - Implement account lockout after failed login attempts
   - Use secure session management
   - Implement CSRF tokens for all state-changing operations

4. **File Uploads**
   - Validate file types and extensions
   - Limit file sizes
   - Store uploaded files outside the web root when possible
   - Scan files for malware if possible

5. **Error Handling**
   - Log errors, don't display them to users in production
   - Use generic error messages for authentication failures
   - Implement proper exception handling

### For Administrators

1. **Environment Configuration**
   - Set `APP_DEBUG=false` in production
   - Use strong, unique passwords (minimum 16 characters)
   - Change default admin codes and credentials
   - Rotate credentials every 90 days

2. **Server Security**
   - Keep PHP, MySQL, and all dependencies up to date
   - Use HTTPS with valid SSL/TLS certificates
   - Configure proper file permissions:
     ```bash
     # Application files
     find /path/to/app -type f -exec chmod 644 {} \;
     find /path/to/app -type d -exec chmod 755 {} \;
     
     # Configuration files
     chmod 600 /path/to/app/.env
     chmod 600 /path/to/app/config/credentials.php
     
     # Writable directories
     chmod 755 /path/to/app/storage
     chmod 755 /path/to/app/logs
     ```

3. **Database Security**
   - Use separate database users with minimal privileges
   - Never use root database user for application
   - Enable MySQL SSL connections
   - Regular database backups
   - Restrict database access to application servers only

4. **Monitoring & Logging**
   - Monitor application logs regularly (`logs/` directory)
   - Set up alerts for suspicious activities
   - Review access logs for unusual patterns
   - Implement intrusion detection if possible

5. **Backups**
   - Regular automated backups of database and files
   - Test backup restoration procedures
   - Store backups securely and encrypted
   - Keep backups off-site

6. **Network Security**
   - Use firewall rules to restrict access
   - Disable unnecessary services and ports
   - Implement rate limiting
   - Use VPN for administrative access

### For Users

1. **Password Security**
   - Use strong, unique passwords
   - Never share your password
   - Change passwords if you suspect compromise
   - Enable two-factor authentication when available

2. **Account Security**
   - Log out when finished using the system
   - Don't use public computers for sensitive operations
   - Report suspicious activity immediately
   - Keep your email account secure (used for password resets)

## Pre-Deployment Security Checklist

Before deploying to production, verify:

- [ ] `APP_DEBUG` is set to `false` in `.env`
- [ ] All default passwords have been changed
- [ ] HTTPS is enabled with valid SSL certificate
- [ ] `.env` file permissions are set to 600
- [ ] Database user has minimal required privileges
- [ ] File upload directory is properly secured
- [ ] Error logging is configured (not error display)
- [ ] Session security is properly configured
- [ ] CSRF protection is implemented on all forms
- [ ] Input validation is in place for all user inputs
- [ ] Rate limiting is configured for authentication
- [ ] Database backups are automated
- [ ] Security monitoring is in place
- [ ] All dependencies are up to date
- [ ] Security headers are configured in web server

## Security Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [MySQL Security](https://dev.mysql.com/doc/refman/8.0/en/security.html)
- [Docker Security](https://docs.docker.com/engine/security/)

## Security Acknowledgments

We thank the following individuals for responsibly disclosing security vulnerabilities:

- (None yet - be the first!)

---

**Last Updated**: February 2026

For security-related questions, contact the development team.
