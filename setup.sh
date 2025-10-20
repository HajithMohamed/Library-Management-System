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

# Check .env
if [ ! -f .env ]; then
  print_status "Creating .env file from .env.example"
  cp .env.example .env
fi

# Stop existing containers
docker-compose down 2>/dev/null || true
print_status "Stopped existing containers"

# Build PHP image locally
docker-compose build php
print_success "PHP image built"

# Start containers
docker-compose up -d
print_success "Containers started"

# Wait for DB
print_status "Waiting for DB to initialize..."
sleep 10

# Install Composer dependencies
docker exec ils_php composer install || print_warning "Composer install failed, run manually inside container"

# Show access info
echo ""
echo "🎉 Setup Complete!"
echo "Application: http://localhost:${WEB_PORT:-8080}"
echo "phpMyAdmin: http://localhost:${PHPMYADMIN_PORT:-8081}"
echo "DB User: ${MYSQL_USER:-library_user}"
echo "DB Password: ${MYSQL_PASSWORD:-library_password}"
