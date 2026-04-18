@echo off
REM =====================================================
REM Mental Health ML Server Startup Script (Windows)
REM =====================================================

setlocal enabledelayedexpansion

REM Get the directory of this script
set SCRIPT_DIR=%~dp0
set PROJECT_DIR=%SCRIPT_DIR:~0,-5%

echo.
echo =====================================================
echo   Mental Health ML Prediction Server
echo =====================================================
echo.
echo Project Directory: %PROJECT_DIR%
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python 3.7+ from https://www.python.org/
    echo Make sure to check "Add Python to PATH" during installation
    pause
    exit /b 1
)

echo [OK] Python found
python --version

REM Check if required packages are installed
echo.
echo Checking required packages...

python -c "import flask" >nul 2>&1
if errorlevel 1 (
    echo.
    echo [*] Flask not found. Installing...
    pip install flask
) else (
    echo [OK] Flask installed
)

python -c "import flask_cors" >nul 2>&1
if errorlevel 1 (
    echo.
    echo [*] Flask-CORS not found. Installing...
    pip install flask-cors
) else (
    echo [OK] Flask-CORS installed
)

python -c "import pickle" >nul 2>&1
if errorlevel 1 (
    echo.
    echo [*] pickle not found. Installing...
    pip install pickle-mixin
) else (
    echo [OK] pickle installed
)

python -c "import numpy" >nul 2>&1
if errorlevel 1 (
    echo.
    echo [*] NumPy not found. Installing...
    pip install numpy
) else (
    echo [OK] NumPy installed
)

python -c "import pandas" >nul 2>&1
if errorlevel 1 (
    echo.
    echo [*] Pandas not found. Installing...
    pip install pandas
) else (
    echo [OK] Pandas installed
)

echo.
echo =====================================================
echo   Starting ML Server...
echo =====================================================
echo.
echo Server will run at: http://localhost:5000
echo API endpoint: http://localhost:5000/api/predict
echo Health check: http://localhost:5000/api/health
echo.
echo Press Ctrl+C to stop the server
echo.

cd /d "%PROJECT_DIR%\api"
python ml_server.py

pause
