# =====================================================
# Mental Health ML Server Startup Script (PowerShell)
# =====================================================

# Get the directory of this script
$ScriptDir = Split-Path -Parent -Path $MyInvocation.MyCommand.Definition
$MLModelDir = Join-Path -Path $ScriptDir -ChildPath "assets\ml_model"

Write-Host ""
Write-Host "=====================================================`n" -ForegroundColor Cyan
Write-Host "  Mental Health ML Prediction Server" -ForegroundColor Yellow
Write-Host "`n=====================================================`n" -ForegroundColor Cyan
Write-Host "ML Model Directory: $MLModelDir`n" -ForegroundColor Gray

# Check if Python is installed
try {
    $PythonVersion = python --version 2>&1
    Write-Host "[OK] $PythonVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Python is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install Python 3.7+ from https://www.python.org/" -ForegroundColor Yellow
    Write-Host "Make sure to check 'Add Python to PATH' during installation`n" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit
}

# Check and install required packages
Write-Host "`nChecking required packages...`n" -ForegroundColor Cyan

$packages = @("flask", "flask_cors", "numpy", "pandas", "sklearn")

foreach ($package in $packages) {
    try {
        $result = python -c "import $package"
        Write-Host "[OK] $package installed" -ForegroundColor Green
    } catch {
        Write-Host "[*] $package not found. Installing..." -ForegroundColor Yellow
        pip install $package
    }
}

Write-Host "`n=====================================================`n" -ForegroundColor Cyan
Write-Host "  Starting ML Server..." -ForegroundColor Yellow
Write-Host "`n=====================================================`n" -ForegroundColor Cyan

Write-Host "Server will run at: http://localhost:5000" -ForegroundColor Cyan
Write-Host "API endpoint: http://localhost:5000/api/predict" -ForegroundColor Cyan
Write-Host "Health check: http://localhost:5000/api/health`n" -ForegroundColor Cyan

Write-Host "Press Ctrl+C to stop the server`n" -ForegroundColor Yellow

# Change directory and start server
Set-Location $MLModelDir
python ml_server.py
