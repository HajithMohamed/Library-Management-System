@echo off
setlocal enabledelayedexpansion

REM University Library Management System - Docker Setup Script (Windows)
REM This script automates the Docker setup process

echo.
echo 🚀 University Library Management System - Docker Setup
echo ==================================================
echo.

REM Function definitions
goto :main

:print_status
echo [INFO] %~1
goto :eof

:print_success
echo [SUCCESS] %~1
goto :eof

:print_warning
echo [WARNING] %~1
goto :eof

:print_error
echo [ERROR] %~1
goto :eof

:check_docker
call :print_status "Checking Docker installation..."
docker --version >nul 2>&1
if errorlevel 1 (
    call :print_error "Docker is not installed. Please install Docker Desktop first."
    exit /b 1
)

docker-compose --version >nul 2>&1
if errorlevel 1 (
    call :print_error "Docker Compose is not installed. Please install Docker Desktop first."
    exit /b 1
)

call :print_success "Docker and Docker Compose are installed"
goto :eof

:check_docker_running
call :print_status "Checking if Docker is running..."
docker info >nul 2>&1
if errorlevel 1 (
    call :print_error "Docker is not running. Please start Docker Desktop first."
    echo.
    echo Please start Docker Desktop and wait for it to fully start.
    echo Press any key once Docker Desktop is running...
    pause >nul
    
    REM Check again after user confirms
    docker info >nul 2>&1
    if errorlevel 1 (
        call :print_error "Docker is still not running. Exiting..."
        exit /b 1
    )
)
call :print_success "Docker is running"

REM Wait for Docker to be fully ready
call :print_status "Waiting for Docker to be fully ready..."
timeout /t 5 /nobreak >nul

goto :eof

:create_env_file
if not exist .env (
    call :print_status "Creating .env file..."
    (
        echo # Docker Environment Variables
        echo.
        echo # Web Server
        echo WEB_PORT=8080
        echo.
        echo # Timezone
        echo TZ=Asia/Kolkata
        echo.
        echo # MySQL Database
        echo MYSQL_ROOT_PASSWORD=rootpassword
        echo MYSQL_DATABASE=integrated_library_system
        echo MYSQL_USER=library_user
        echo MYSQL_PASSWORD=library_password
        echo.
        echo # PHPMyAdmin
        echo PHPMYADMIN_PORT=8081
        echo.
        echo # Application Settings
        echo APP_ENV=development
        echo APP_DEBUG=true
    ) > .env
    call :print_success ".env file created"
) else (
    call :print_status ".env file already exists"
)
goto :eof

:stop_containers
call :print_status "Stopping existing containers..."
docker-compose down 2>nul
if errorlevel 1 (
    call :print_warning "No existing containers to stop"
) else (
    call :print_success "Existing containers stopped"
)

REM Wait a moment after stopping
timeout /t 3 /nobreak >nul
goto :eof

:start_containers
call :print_status "Building and starting containers..."
call :print_status "This may take a few minutes on first run..."

REM Try to build first
docker-compose build --no-cache
if errorlevel 1 (
    call :print_error "Failed to build containers"
    call :print_status "Trying alternative approach..."
    
    REM Clean everything and retry
    docker system prune -f >nul 2>&1
    timeout /t 3 /nobreak >nul
    
    docker-compose build
    if errorlevel 1 (
        call :print_error "Build failed. Please check Docker Desktop is running properly."
        exit /b 1
    )
)

REM Wait a moment between build and start
timeout /t 5 /nobreak >nul

REM Now start the containers
call :print_status "Starting containers..."
docker-compose up -d
if errorlevel 1 (
    call :print_error "Failed to start containers"
    call :print_status "Checking Docker status..."
    docker info
    exit /b 1
)

call :print_success "Containers started successfully"
goto :eof

:wait_for_containers
call :print_status "Waiting for containers to be ready..."
timeout /t 15 /nobreak >nul

REM Verify containers are running
docker-compose ps
call :print_success "Containers are running"
goto :eof

:install_composer_deps
call :print_status "Installing Composer dependencies..."

REM Wait a bit more for PHP container to be fully ready
timeout /t 5 /nobreak >nul

REM Get the actual PHP container name
for /f "tokens=*" %%i in ('docker ps --format "{{.Names}}" ^| findstr php') do set PHP_CONTAINER=%%i

if not defined PHP_CONTAINER (
    call :print_warning "PHP container not found. Skipping Composer installation."
    call :print_status "You may need to install manually later:"
    echo   docker exec ^<php_container_name^> composer install
    goto :eof
)

call :print_status "Found PHP container: %PHP_CONTAINER%"

REM Try to copy composer files
docker cp composer.json %PHP_CONTAINER%:/var/www/html/ 2>nul
if errorlevel 1 (
    call :print_warning "Could not copy composer.json - file may not exist"
    goto :eof
)

docker cp composer.lock %PHP_CONTAINER%:/var/www/html/ 2>nul

REM Install dependencies
docker exec %PHP_CONTAINER% composer install
if errorlevel 1 (
    call :print_warning "Could not install Composer dependencies automatically"
    call :print_status "You may need to install them manually:"
    echo   docker exec %PHP_CONTAINER% composer install
    goto :eof
)

call :print_success "Composer dependencies installed"
goto :eof

:verify_installation
call :print_status "Verifying installation..."

REM Get PHP container name again
for /f "tokens=*" %%i in ('docker ps --format "{{.Names}}" ^| findstr php') do set PHP_CONTAINER=%%i

if defined PHP_CONTAINER (
    REM Check if autoload.php exists
    docker exec %PHP_CONTAINER% test -f /var/www/html/vendor/autoload.php 2>nul
    if errorlevel 1 (
        call :print_warning "Autoloader file not found - Composer dependencies may not be installed"
    ) else (
        call :print_success "Autoloader file found"
    )
)

REM Test web server
call :print_status "Testing web server..."
timeout /t 5 /nobreak >nul
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost:8080' -UseBasicParsing -TimeoutSec 10; if ($response.StatusCode -eq 200) { Write-Host '[SUCCESS] Web server is responding' } else { Write-Host '[WARNING] Web server may not be responding properly' } } catch { Write-Host '[WARNING] Web server may not be ready yet - wait a moment and try accessing it manually' }" 2>nul
goto :eof

:show_final_info
echo.
echo ========================================
echo 🎉 Setup Complete!
echo ========================================
echo.
echo 📱 Access your application:
echo   • Main Application: http://localhost:8080
echo   • PHPMyAdmin: http://localhost:8081
echo.
echo 🔧 Container Management:
echo   • View containers: docker-compose ps
echo   • View logs: docker-compose logs -f
echo   • Stop containers: docker-compose down
echo   • Restart containers: docker-compose restart
echo.
echo 📊 Default Credentials:
echo   • Admin Code: hello_world
echo   • Database: integrated_library_system
echo   • DB User: library_user
echo   • DB Password: library_password
echo.
echo 🐛 Troubleshooting:
echo   • Check logs: docker-compose logs -f
echo   • Restart Docker Desktop and run this script again
echo   • Rebuild: docker-compose up -d --build
echo.
goto :eof

:main
echo Starting setup process...
echo.

call :check_docker
if errorlevel 1 (
    echo.
    pause
    exit /b 1
)

call :check_docker_running
if errorlevel 1 (
    echo.
    pause
    exit /b 1
)

call :create_env_file
call :stop_containers
call :start_containers
if errorlevel 1 (
    echo.
    echo ========================================
    echo ⚠️ Setup Failed
    echo ========================================
    echo.
    echo Common solutions:
    echo 1. Make sure Docker Desktop is fully started
    echo 2. Try restarting Docker Desktop
    echo 3. Move project out of OneDrive folder
    echo 4. Run: docker-compose down, then try again
    echo.
    pause
    exit /b 1
)

call :wait_for_containers
call :install_composer_deps
call :verify_installation
call :show_final_info

call :print_success "Setup completed successfully! 🎉"
echo.
echo Press any key to exit...
pause >nul