#!/bin/bash

# University Library Management System - Docker Setup Script
# This script automates the Docker setup process

set -e  # Exit on any error

echo "🚀 University Library Management System - Docker Setup"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is installed
check_docker() {
    print_status "Checking Docker installation..."
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    print_success "Docker and Docker Compose are installed"
}

# Check if Docker is running
check_docker_running() {
    print_status "Checking if Docker is running..."
    if ! docker info &> /dev/null; then
        print_error "Docker is not running. Please start Docker first."
        exit 1
    fi
    print_success "Docker is running"
}

# Create .env file if it doesn't exist
create_env_file() {
    if [ ! -f .env ]; then
        print_status "Creating .env file..."
        cat > .env << EOF
# Docker Environment Variables

# Web Server
WEB_PORT=8080

# Timezone
TZ=Asia/Kolkata

# MySQL Database
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=integrated_library_system
MYSQL_USER=library_user
MYSQL_PASSWORD=library_password

# PHPMyAdmin
PHPMYADMIN_PORT=8081

# Application Settings
APP_ENV=development
APP_DEBUG=true
EOF
        print_success ".env file created"
    else
        print_status ".env file already exists"
    fi
}

# Stop existing containers
stop_containers() {
    print_status "Stopping existing containers..."
    docker-compose down 2>/dev/null || true
    print_success "Existing containers stopped"
}

# Build and start containers
start_containers() {
    print_status "Building and starting containers..."
    docker-compose up -d --build
    
    if [ $? -eq 0 ]; then
        print_success "Containers started successfully"
    else
        print_error "Failed to start containers"
        exit 1
    fi
}

# Wait for containers to be ready
wait_for_containers() {
    print_status "Waiting for containers to be ready..."
    sleep 10
    
    # Check if containers are running
    if docker-compose ps | grep -q "Up"; then
        print_success "Containers are running"
    else
        print_warning "Some containers may not be running properly"
    fi
}

# Install Composer dependencies
install_composer_deps() {
    print_status "Installing Composer dependencies..."
    
    # Copy composer files to container
    docker cp composer.json ils_php:/var/www/html/ 2>/dev/null || {
        print_warning "Could not copy composer.json, trying alternative container name..."
        docker cp composer.json lms-php:/var/www/html/ 2>/dev/null || {
            print_error "Could not copy composer.json to container"
            return 1
        }
    }
    
    docker cp composer.lock ils_php:/var/www/html/ 2>/dev/null || {
        docker cp composer.lock lms-php:/var/www/html/ 2>/dev/null || true
    }
    
    # Install dependencies
    docker exec ils_php composer install 2>/dev/null || {
        docker exec lms-php composer install 2>/dev/null || {
            print_warning "Could not install Composer dependencies automatically"
            print_status "You may need to install them manually:"
            echo "  docker exec <container_name> composer install"
            return 1
        }
    }
    
    print_success "Composer dependencies installed"
}

# Verify installation
verify_installation() {
    print_status "Verifying installation..."
    
    # Check if autoload.php exists
    if docker exec ils_php test -f /var/www/html/vendor/autoload.php 2>/dev/null || \
       docker exec lms-php test -f /var/www/html/vendor/autoload.php 2>/dev/null; then
        print_success "Autoloader file found"
    else
        print_warning "Autoloader file not found - Composer dependencies may not be installed"
    fi
    
    # Test web server
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "200"; then
        print_success "Web server is responding"
    else
        print_warning "Web server may not be responding properly"
    fi
}

# Show final information
show_final_info() {
    echo ""
    echo "🎉 Setup Complete!"
    echo "=================="
    echo ""
    echo "📱 Access your application:"
    echo "  • Main Application: http://localhost:8080"
    echo "  • PHPMyAdmin: http://localhost:8081"
    echo ""
    echo "🔧 Container Management:"
    echo "  • View containers: docker-compose ps"
    echo "  • View logs: docker-compose logs"
    echo "  • Stop containers: docker-compose down"
    echo "  • Restart containers: docker-compose restart"
    echo ""
    echo "📊 Default Credentials:"
    echo "  • Admin Code: hello_world"
    echo "  • Database: integrated_library_system"
    echo "  • DB User: library_user"
    echo "  • DB Password: library_password"
    echo ""
    echo "🐛 Troubleshooting:"
    echo "  • Check logs: docker logs ils_php"
    echo "  • Install dependencies: docker exec ils_php composer install"
    echo "  • Rebuild: docker-compose up -d --build"
    echo ""
}

# Main execution
main() {
    echo "Starting setup process..."
    echo ""
    
    check_docker
    check_docker_running
    create_env_file
    stop_containers
    start_containers
    wait_for_containers
    install_composer_deps
    verify_installation
    show_final_info
    
    print_success "Setup completed successfully! 🎉"
}

# Run main function
main "$@"
