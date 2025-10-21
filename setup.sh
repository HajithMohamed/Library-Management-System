#!/bin/bash
# University Library Management System - Docker Setup Script
set -e

echo "🚀 University Library Management System - Docker Setup"
echo "=================================================="

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

# Check Docker
if ! command -v docker &>/dev/null; then
  print_error "Docker not installed"
  exit 1
fi

if ! command -v docker-compose &>/dev/null; then
  print_error "Docker Compose not installed"
  exit 1
fi

print_success "Docker & Docker Compose installed"

# Check Docker running
if ! docker info &>/dev/null; then
  print_error "Docker not running"
  exit 1
fi

print_success "Docker is running"

# Check and fix .env file
if [ ! -f .env ]; then
  print_status "Creating .env file..."

  if [ -f .env.example ]; then
    # Check if .env.example has encoding issues
    if file .env.example | grep -q "UTF-16"; then
      print_warning ".env.example has UTF-16 encoding, converting to UTF-8..."
      if command -v iconv &>/dev/null; then
        iconv -f UTF-16 -t UTF-8 .env.example > .env
      else
        print_error "iconv not found. Cannot convert encoding."
        exit 1
      fi
    else
      cp .env.example .env
    fi
  else
    print_warning ".env.example not found, creating default .env file..."
    cat > .env << 'EOF'
# Environment Configuration for Library Management System

# Application Settings
APP_NAME="University Library Management System"
APP_ENV=production
APP_DEBUG=false

# Web Server
WEB_PORT=8080

# Database Configuration
MYSQL_HOST=mysql
MYSQL_PORT=3306
MYSQL_DATABASE=library_db
MYSQL_USER=library_user
MYSQL_PASSWORD=library_password
MYSQL_ROOT_PASSWORD=root_password

# phpMyAdmin
PHPMYADMIN_PORT=8081

# PHP Configuration
PHP_VERSION=8.2
UPLOAD_MAX_FILESIZE=10M
POST_MAX_SIZE=10M
MEMORY_LIMIT=256M

# Session Configuration
SESSION_LIFETIME=120

# Timezone
TIMEZONE=Asia/Colombo
EOF
  fi
  print_success ".env file created"
else
  # Check if existing .env has encoding issues
  if file .env | grep -q "UTF-16"; then
    print_warning ".env has UTF-16 encoding, converting to UTF-8..."
    if command -v iconv &>/dev/null; then
      iconv -f UTF-16 -t UTF-8 .env > .env.tmp
      mv .env.tmp .env
      print_success ".env encoding fixed"
    else
      print_error "iconv not found. Please recreate .env file manually."
      exit 1
    fi
  fi
fi

# Verify .env is readable
if ! grep -q "MYSQL_DATABASE" .env 2>/dev/null; then
  print_error ".env file is corrupted or incomplete"
  print_status "Please delete .env and run this script again"
  exit 1
fi

print_success ".env file is valid"

# Stop existing containers
print_status "Stopping existing containers..."
docker-compose down 2>/dev/null || true
print_success "Stopped existing containers"

# Build PHP image locally
print_status "Building PHP image..."
docker-compose build php
print_success "PHP image built"

# Start containers
print_status "Starting containers..."
docker-compose up -d
print_success "Containers started"

# Wait for DB
print_status "Waiting for database to initialize..."
sleep 10

# Check if containers are running
if ! docker-compose ps | grep -q "Up"; then
  print_error "Containers failed to start. Check docker-compose logs"
  docker-compose logs
  exit 1
fi

# Install Composer dependencies
print_status "Installing Composer dependencies..."
if docker exec ils_php composer install 2>/dev/null; then
  print_success "Composer dependencies installed"
else
  print_warning "Composer install failed. You may need to run it manually:"
  print_warning "  docker exec -it ils_php composer install"
fi

# Load environment variables
source .env 2>/dev/null || true

# Show access info
echo ""
echo "🎉 Setup Complete!"
echo "=================================================="
echo "📱 Application:  http://localhost:${WEB_PORT:-8080}"
echo "🗄️  phpMyAdmin:   http://localhost:${PHPMYADMIN_PORT:-8081}"
echo "👤 DB User:      ${MYSQL_USER:-library_user}"
echo "🔑 DB Password:  ${MYSQL_PASSWORD:-library_password}"
echo "=================================================="
echo ""
echo "📝 Useful commands:"
echo "  View logs:     docker-compose logs -f"
echo "  Stop:          docker-compose down"
echo "  Restart:       docker-compose restart"
echo "  Enter PHP:     docker exec -it ils_php bash"
echo "  Enter MySQL:   docker exec -it ils_mysql mysql -u root -p"
echo ""
