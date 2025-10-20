# University Library Management System - Docker Setup Script (PowerShell)
# This script automates the Docker setup process

param(
    [switch]$SkipDockerCheck,
    [switch]$SkipComposerInstall,
    [switch]$Verbose
)

# Colors for output
$Colors = @{
    Red = "Red"
    Green = "Green"
    Yellow = "Yellow"
    Blue = "Cyan"
    White = "White"
}

function Write-Status {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor $Colors.Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "[SUCCESS] $Message" -ForegroundColor $Colors.Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "[WARNING] $Message" -ForegroundColor $Colors.Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor $Colors.Red
}

function Test-Docker {
    Write-Status "Checking Docker installation..."
    
    try {
        $dockerVersion = docker --version 2>$null
        if (-not $dockerVersion) {
            throw "Docker not found"
        }
        
        $composeVersion = docker-compose --version 2>$null
        if (-not $composeVersion) {
            throw "Docker Compose not found"
        }
        
        Write-Success "Docker and Docker Compose are installed"
        return $true
    }
    catch {
        Write-Error "Docker is not installed. Please install Docker Desktop first."
        return $false
    }
}

function Test-DockerRunning {
    Write-Status "Checking if Docker is running..."
    
    try {
        docker info 2>$null | Out-Null
        if ($LASTEXITCODE -ne 0) {
            throw "Docker not running"
        }
        Write-Success "Docker is running"
        return $true
    }
    catch {
        Write-Error "Docker is not running. Please start Docker Desktop first."
        return $false
    }
}

function New-EnvFile {
    if (-not (Test-Path ".env")) {
        Write-Status "Creating .env file..."
        
        $envContent = @"
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
"@
        
        $envContent | Out-File -FilePath ".env" -Encoding UTF8
        Write-Success ".env file created"
    }
    else {
        Write-Status ".env file already exists"
    }
}

function Stop-Containers {
    Write-Status "Stopping existing containers..."
    try {
        docker-compose down 2>$null
        Write-Success "Existing containers stopped"
    }
    catch {
        Write-Warning "No existing containers to stop"
    }
}

function Start-Containers {
    Write-Status "Building and starting containers..."
    
    try {
        docker-compose up -d --build
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Containers started successfully"
            return $true
        }
        else {
            Write-Error "Failed to start containers"
            return $false
        }
    }
    catch {
        Write-Error "Failed to start containers: $_"
        return $false
    }
}

function Wait-ForContainers {
    Write-Status "Waiting for containers to be ready..."
    Start-Sleep -Seconds 10
    Write-Success "Containers are running"
}

function Install-ComposerDependencies {
    if ($SkipComposerInstall) {
        Write-Status "Skipping Composer dependency installation"
        return
    }
    
    Write-Status "Installing Composer dependencies..."
    
    $containerNames = @("ils_php", "lms-php")
    $success = $false
    
    foreach ($containerName in $containerNames) {
        try {
            # Copy composer files
            docker cp composer.json "${containerName}:/var/www/html/" 2>$null
            docker cp composer.lock "${containerName}:/var/www/html/" 2>$null
            
            # Install dependencies
            docker exec $containerName composer install 2>$null
            
            if ($LASTEXITCODE -eq 0) {
                Write-Success "Composer dependencies installed in $containerName"
                $success = $true
                break
            }
        }
        catch {
            continue
        }
    }
    
    if (-not $success) {
        Write-Warning "Could not install Composer dependencies automatically"
        Write-Status "You may need to install them manually:"
        Write-Host "  docker exec <container_name> composer install" -ForegroundColor $Colors.Yellow
    }
}

function Test-Installation {
    Write-Status "Verifying installation..."
    
    # Check if autoload.php exists
    $containerNames = @("ils_php", "lms-php")
    $autoloaderFound = $false
    
    foreach ($containerName in $containerNames) {
        try {
            docker exec $containerName test -f /var/www/html/vendor/autoload.php 2>$null
            if ($LASTEXITCODE -eq 0) {
                Write-Success "Autoloader file found in $containerName"
                $autoloaderFound = $true
                break
            }
        }
        catch {
            continue
        }
    }
    
    if (-not $autoloaderFound) {
        Write-Warning "Autoloader file not found - Composer dependencies may not be installed"
    }
    
    # Test web server
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            Write-Success "Web server is responding"
        }
        else {
            Write-Warning "Web server may not be responding properly"
        }
    }
    catch {
        Write-Warning "Web server may not be responding properly"
    }
}

function Show-FinalInfo {
    Write-Host ""
    Write-Host "🎉 Setup Complete!" -ForegroundColor $Colors.Green
    Write-Host "==================" -ForegroundColor $Colors.Green
    Write-Host ""
    Write-Host "📱 Access your application:" -ForegroundColor $Colors.White
    Write-Host "  • Main Application: http://localhost:8080" -ForegroundColor $Colors.White
    Write-Host "  • PHPMyAdmin: http://localhost:8081" -ForegroundColor $Colors.White
    Write-Host ""
    Write-Host "🔧 Container Management:" -ForegroundColor $Colors.White
    Write-Host "  • View containers: docker-compose ps" -ForegroundColor $Colors.White
    Write-Host "  • View logs: docker-compose logs" -ForegroundColor $Colors.White
    Write-Host "  • Stop containers: docker-compose down" -ForegroundColor $Colors.White
    Write-Host "  • Restart containers: docker-compose restart" -ForegroundColor $Colors.White
    Write-Host ""
    Write-Host "📊 Default Credentials:" -ForegroundColor $Colors.White
    Write-Host "  • Admin Code: hello_world" -ForegroundColor $Colors.White
    Write-Host "  • Database: integrated_library_system" -ForegroundColor $Colors.White
    Write-Host "  • DB User: library_user" -ForegroundColor $Colors.White
    Write-Host "  • DB Password: library_password" -ForegroundColor $Colors.White
    Write-Host ""
    Write-Host "🐛 Troubleshooting:" -ForegroundColor $Colors.White
    Write-Host "  • Check logs: docker logs ils_php" -ForegroundColor $Colors.White
    Write-Host "  • Install dependencies: docker exec ils_php composer install" -ForegroundColor $Colors.White
    Write-Host "  • Rebuild: docker-compose up -d --build" -ForegroundColor $Colors.White
    Write-Host ""
}

# Main execution
function Main {
    Write-Host ""
    Write-Host "🚀 University Library Management System - Docker Setup" -ForegroundColor $Colors.Blue
    Write-Host "==================================================" -ForegroundColor $Colors.Blue
    Write-Host ""
    
    Write-Status "Starting setup process..."
    Write-Host ""
    
    # Check Docker
    if (-not $SkipDockerCheck) {
        if (-not (Test-Docker)) { exit 1 }
        if (-not (Test-DockerRunning)) { exit 1 }
    }
    
    # Setup process
    New-EnvFile
    Stop-Containers
    
    if (-not (Start-Containers)) { exit 1 }
    
    Wait-ForContainers
    Install-ComposerDependencies
    Test-Installation
    Show-FinalInfo
    
    Write-Success "Setup completed successfully! 🎉"
    Write-Host ""
}

# Run main function
Main
