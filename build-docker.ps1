# PowerShell script to build Docker containers with retry logic
Write-Host "Building Docker containers with retry logic..." -ForegroundColor Green

# Function to retry Docker commands
function Invoke-DockerWithRetry {
    param(
        [string]$Command,
        [int]$MaxRetries = 3,
        [int]$DelaySeconds = 10
    )
    
    for ($i = 1; $i -le $MaxRetries; $i++) {
        Write-Host "Attempt $i of $MaxRetries: $Command" -ForegroundColor Yellow
        
        try {
            Invoke-Expression $Command
            if ($LASTEXITCODE -eq 0) {
                Write-Host "Command succeeded on attempt $i" -ForegroundColor Green
                return
            }
        }
        catch {
            Write-Host "Command failed on attempt $i: $($_.Exception.Message)" -ForegroundColor Red
        }
        
        if ($i -lt $MaxRetries) {
            Write-Host "Waiting $DelaySeconds seconds before retry..." -ForegroundColor Yellow
            Start-Sleep -Seconds $DelaySeconds
        }
    }
    
    Write-Host "Command failed after $MaxRetries attempts" -ForegroundColor Red
    exit 1
}

# Stop and remove existing containers
Write-Host "Stopping existing containers..." -ForegroundColor Blue
docker-compose down

# Remove any existing images to force rebuild
Write-Host "Removing existing images..." -ForegroundColor Blue
docker-compose down --rmi all

# Build with retry logic
Write-Host "Building containers..." -ForegroundColor Blue
Invoke-DockerWithRetry "docker-compose build --no-cache"

# Start containers
Write-Host "Starting containers..." -ForegroundColor Blue
Invoke-DockerWithRetry "docker-compose up -d"

Write-Host "Docker build completed successfully!" -ForegroundColor Green
Write-Host "You can now access your application at http://localhost:8080" -ForegroundColor Cyan
