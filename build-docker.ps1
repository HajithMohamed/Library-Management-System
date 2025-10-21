# PowerShell script with proper error handling for Docker Desktop
Write-Host "`n🚀 University Library Management System - Docker Setup" -ForegroundColor Cyan
Write-Host "===================================================`n" -ForegroundColor Cyan

# Function to check if Docker Desktop is running
function Test-DockerRunning {
    try {
        $null = docker info 2>&1
        return $LASTEXITCODE -eq 0
    }
    catch {
        return $false
    }
}

# Function to wait for Docker to be ready
function Wait-DockerReady {
    Write-Host "⏳ Waiting for Docker Desktop to be ready..." -ForegroundColor Yellow
    $maxAttempts = 30
    $attempt = 0
    
    while ($attempt -lt $maxAttempts) {
        if (Test-DockerRunning) {
            Write-Host "✓ Docker is ready!" -ForegroundColor Green
            Start-Sleep -Seconds 2  # Extra buffer time
            return $true
        }
        
        Write-Host "   Waiting... ($($attempt + 1)/$maxAttempts)" -ForegroundColor Gray
        Start-Sleep -Seconds 2
        $attempt++
    }
    
    Write-Host "✗ Docker failed to start in time" -ForegroundColor Red
    return $false
}

# Check if Docker Desktop is running
if (-not (Test-DockerRunning)) {
    Write-Host "⚠️  Docker Desktop is not running!" -ForegroundColor Red
    Write-Host "   Please start Docker Desktop and wait for it to fully start." -ForegroundColor Yellow
    Write-Host "   Press any key once Docker Desktop is running..." -ForegroundColor Yellow
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    
    if (-not (Wait-DockerReady)) {
        Write-Host "`n✗ Setup aborted - Docker is not ready" -ForegroundColor Red
        exit 1
    }
}

# Create .env file if it doesn't exist
if (-not (Test-Path ".env")) {
    Write-Host "`n📝 Creating .env file..." -ForegroundColor Blue
    
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
    Write-Host "✓ .env file created" -ForegroundColor Green
}

# Clean up existing containers
Write-Host "`n🧹 Cleaning up existing containers..." -ForegroundColor Blue
docker-compose down 2>&1 | Out-Null

# Wait a moment for cleanup
Start-Sleep -Seconds 3

# Build and start containers
Write-Host "`n🔨 Building and starting containers..." -ForegroundColor Blue
Write-Host "   This may take a few minutes..." -ForegroundColor Gray

$buildProcess = Start-Process -FilePath "docker-compose" -ArgumentList "up -d --build" -NoNewWindow -PassThru -Wait

if ($buildProcess.ExitCode -ne 0) {
    Write-Host "`n✗ Failed to start containers" -ForegroundColor Red
    Write-Host "`nTrying alternative approach..." -ForegroundColor Yellow
    
    # Try without cache
    Write-Host "Building without cache..." -ForegroundColor Blue
    docker-compose build --no-cache
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "✗ Build failed" -ForegroundColor Red
        exit 1
    }
    
    Start-Sleep -Seconds 2
    
    Write-Host "Starting containers..." -ForegroundColor Blue
    docker-compose up -d
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "✗ Failed to start containers" -ForegroundColor Red
        exit 1
    }
}

# Wait for containers to be ready
Write-Host "`n⏳ Waiting for containers to initialize..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Show running containers
Write-Host "`n📦 Running containers:" -ForegroundColor Blue
docker-compose ps

# Final information
Write-Host "`n🎉 Setup Complete!" -ForegroundColor Green
Write-Host "==================`n" -ForegroundColor Green
Write-Host "📱 Access your application:" -ForegroundColor Cyan
Write-Host "   • Main Application: http://localhost:8080" -ForegroundColor White
Write-Host "   • PHPMyAdmin: http://localhost:8081`n" -ForegroundColor White
Write-Host "🔧 Useful Commands:" -ForegroundColor Cyan
Write-Host "   • View logs: docker-compose logs -f" -ForegroundColor White
Write-Host "   • Stop: docker-compose down" -ForegroundColor White
Write-Host "   • Restart: docker-compose restart`n" -ForegroundColor White

Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")