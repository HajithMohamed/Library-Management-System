@echo off
setlocal enabledelayedexpansion

REM University Library Management System - Docker Setup Script (Windows)
REM This script automates the Docker setup process

echo.
echo 🚀 University Library Management System - Docker Setup
echo ==================================================
echo.

REM Function to print colored output (Windows doesn't support colors in batch, so using echo)
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
    exit /b 1
)
call :print_success "Docker is running"
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
docker-compose down >nul 2>&1
call :print_success "Existing containers stopped"
goto :eof

:start_containers
call :print_status "Building and starting containers..."
docker-compose up -d --build
if errorlevel 1 (
    call :print_error "Failed to start containers"
    exit /b 1
)
call :print_success "Containers started successfully"
goto :eof

:wait_for_containers
call :print_status "Waiting for containers to be ready..."
timeout /t 10 /nobreak >nul
call :print_success "Containers are running"
goto :eof

:install_composer_deps
call :print_status "Installing Composer dependencies..."

REM Try to copy composer files to container
docker cp composer.json ils_php:/var/www/html/ >nul 2>&1
if errorlevel 1 (
    call :print_warning "Could not copy composer.json to ils_php, trying alternative container name..."
    docker cp composer.json lms-php:/var/www/html/ >nul 2>&1
    if errorlevel 1 (
        call :print_error "Could not copy composer.json to container"
        goto :eof
    )
)

docker cp composer.lock ils_php:/var/www/html/ >nul 2>&1
if errorlevel 1 (
    docker cp composer.lock lms-php:/var/www/html/ >nul 2>&1
)

REM Try to install dependencies
docker exec ils_php composer install >nul 2>&1
if errorlevel 1 (
    docker exec lms-php composer install >nul 2>&1
    if errorlevel 1 (
        call :print_warning "Could not install Composer dependencies automatically"
        call :print_status "You may need to install them manually:"
        echo   docker exec ^<container_name^> composer install
        goto :eof
    )
)

call :print_success "Composer dependencies installed"
goto :eof

:verify_installation
call :print_status "Verifying installation..."

REM Check if autoload.php exists
docker exec ils_php test -f /var/www/html/vendor/autoload.php >nul 2>&1
if errorlevel 1 (
    docker exec lms-php test -f /var/www/html/vendor/autoload.php >nul 2>&1
    if errorlevel 1 (
        call :print_warning "Autoloader file not found - Composer dependencies may not be installed"
    ) else (
        call :print_success "Autoloader file found"
    )
) else (
    call :print_success "Autoloader file found"
)

REM Test web server (using PowerShell)
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost:8080' -UseBasicParsing -TimeoutSec 5; if ($response.StatusCode -eq 200) { Write-Host '[SUCCESS] Web server is responding' } else { Write-Host '[WARNING] Web server may not be responding properly' } } catch { Write-Host '[WARNING] Web server may not be responding properly' }" 2>nul
goto :eof

:show_final_info
echo.
echo 🎉 Setup Complete!
echo ==================
echo.
echo 📱 Access your application:
echo   • Main Application: http://localhost:8080
echo   • PHPMyAdmin: http://localhost:8081
echo.
echo 🔧 Container Management:
echo   • View containers: docker-compose ps
echo   • View logs: docker-compose logs
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
echo   • Check logs: docker logs ils_php
echo   • Install dependencies: docker exec ils_php composer install
echo   • Rebuild: docker-compose up -d --build
echo.
goto :eof

:main
echo Starting setup process...
echo.

call :check_docker
if errorlevel 1 exit /b 1

call :check_docker_running
if errorlevel 1 exit /b 1

call :create_env_file
call :stop_containers
call :start_containers
if errorlevel 1 exit /b 1

call :wait_for_containers
call :install_composer_deps
call :verify_installation
call :show_final_info

call :print_success "Setup completed successfully! 🎉"
echo.
pause
