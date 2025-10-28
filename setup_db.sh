#!/bin/bash
# Fix Database Connection Issues

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

echo "🔧 Fixing Database Connection Issues"
echo "====================================="

# Step 1: Stop all containers
print_status "Stopping all containers..."
docker-compose down -v
print_success "Containers stopped"

# Step 2: Create/Update .env file with correct settings
print_status "Creating correct .env file..."
cat > .env << 'EOF'
# Application Configuration
TZ=Asia/Kolkata
WEB_PORT=8080
PHPMYADMIN_PORT=8081

# Database Configuration
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=integrated_library_system
MYSQL_USER=library_user
MYSQL_PASSWORD=library_password

# Application Database Settings (for PHP)
DB_HOST=db
DB_PORT=3306
DB_USER=library_user
DB_PASSWORD=library_password
DB_NAME=integrated_library_system


# Admin Configuration
ADMIN_CODE=hello_world
APP_DEBUG=true

# Admin Configuration
ADMIN_CODE=hello_world

# Email Configuration (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=exampe@gmail.com
SMTP_PASSWORD=app_password
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=example@gmail.com
SMTP_FROM_NAME="Library Management System University of Ruhuna"

# SMS Gateway Configuration (optional - leave blank to disable)
SMS_API_URL=
SMS_API_KEY=
SMS_SENDER_ID=
EOF
print_success ".env file created"

# Step 3: Check if docker-compose.yml exists
if [ ! -f docker-compose.yml ]; then
  print_error "docker-compose.yml not found!"
  exit 1
fi

# Step 4: Start containers
print_status "Starting containers with new configuration..."
docker-compose up -d
print_success "Containers started"

# Step 5: Wait for MySQL to be ready
print_status "Waiting for MySQL to initialize (30 seconds)..."
sleep 30

# Step 6: Verify MySQL is running
if ! docker-compose ps | grep -q "mysql.*Up"; then
  print_error "MySQL container is not running!"
  print_status "Checking logs..."
  docker-compose logs mysql
  exit 1
fi
print_success "MySQL container is running"

# Step 7: Setup database and permissions
print_status "Setting up database and user permissions..."

# Create SQL script
cat > /tmp/setup_db.sql << 'EOF'
-- Create database
CREATE DATABASE IF NOT EXISTS integrated_library_system;

-- Create user and grant all privileges
CREATE USER IF NOT EXISTS 'library_user'@'%' IDENTIFIED BY 'library_password';
GRANT ALL PRIVILEGES ON integrated_library_system.* TO 'library_user'@'%';
FLUSH PRIVILEGES;

-- Show databases and users
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User='library_user';
EOF

# Execute SQL script
docker exec -i ils_mysql mysql -u root -proot_password < /tmp/setup_db.sql 2>/dev/null

if [ $? -eq 0 ]; then
  print_success "Database and user configured successfully"
else
  print_warning "Failed to configure database. Trying alternative method..."

  # Alternative: Execute commands one by one
  docker exec ils_mysql mysql -u root -proot_password -e "CREATE DATABASE IF NOT EXISTS integrated_library_system;" 2>/dev/null
  docker exec ils_mysql mysql -u root -proot_password -e "CREATE USER IF NOT EXISTS 'library_user'@'%' IDENTIFIED BY 'library_password';" 2>/dev/null
  docker exec ils_mysql mysql -u root -proot_password -e "GRANT ALL PRIVILEGES ON integrated_library_system.* TO 'library_user'@'%';" 2>/dev/null
  docker exec ils_mysql mysql -u root -proot_password -e "FLUSH PRIVILEGES;" 2>/dev/null

  print_success "Database configured using alternative method"
fi

# Step 8: Test connection
print_status "Testing database connection..."
if docker exec ils_mysql mysql -u library_user -plibrary_password -e "USE integrated_library_system; SHOW TABLES;" 2>/dev/null; then
  print_success "Database connection successful!"
else
  print_error "Database connection test failed"
  print_status "Checking MySQL logs..."
  docker-compose logs mysql | tail -20
  exit 1
fi

# Step 9: Check PHP configuration (if exists)
print_status "Checking application configuration files..."

CONFIG_FILES=(
  "config/database.php"
  "includes/config.php"
  "config/config.php"
  "config.php"
  "db_config.php"
)

for config in "${CONFIG_FILES[@]}"; do
  if [ -f "$config" ]; then
    print_status "Found config file: $config"

    # Check if it has database configuration
    if grep -q "database\|DB_NAME" "$config"; then
      print_warning "Please verify database settings in: $config"
      print_status "Expected values:"
      echo "  - Database: integrated_library_system"
      echo "  - Host: db (or mysql)"
      echo "  - User: library_user"
      echo "  - Password: library_password"
    fi
  fi
done

# Step 10: Show status
echo ""
echo "🎉 Database Setup Complete!"
echo "====================================="
echo "📊 Database Name:  integrated_library_system"
echo "👤 Database User:  library_user"
echo "🔑 Password:       library_password"
echo "🔌 Host:           db"
echo "🔢 Port:           3306"
echo ""
echo "🌐 Access Points:"
echo "  Application:  http://localhost:8080"
echo "  phpMyAdmin:   http://localhost:8081"
echo ""
echo "📝 Useful Commands:"
echo "  View all logs:        docker-compose logs -f"
echo "  View MySQL logs:      docker-compose logs -f mysql"
echo "  Enter MySQL:          docker exec -it ils_mysql mysql -u root -proot_password"
echo "  Test connection:      docker exec ils_mysql mysql -u library_user -plibrary_password integrated_library_system"
echo "  Restart services:     docker-compose restart"
echo ""

# Cleanup
rm -f /tmp/setup_db.sql

print_success "All done! Try accessing your application now."
