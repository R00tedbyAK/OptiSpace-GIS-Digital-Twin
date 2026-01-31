@echo off
setlocal enabledelayedexpansion
color 0A
title OptiSpace - Smart Parking System Launcher

:: ============================================================================
:: OPTISPACE ONE-CLICK LAUNCHER
:: Automated setup and launch script for hackathon judges
:: ============================================================================

echo.
echo ╔════════════════════════════════════════════════════════════════╗
echo ║                  OPTISPACE COMMAND CENTER                      ║
echo ║              Smart Parking Management System                   ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.

:: ============================================================================
:: STEP 1: PREREQUISITE CHECK
:: ============================================================================
echo [1/4] Checking PHP Environment...

php --version >nul 2>&1
if errorlevel 1 (
    color 0C
    echo.
    echo ╔════════════════════════════════════════════════════════════════╗
    echo ║  ERROR: PHP ENVIRONMENT NOT FOUND                              ║
    echo ╚════════════════════════════════════════════════════════════════╝
    echo.
    echo  PHP is not installed or not in system PATH.
    echo  Launching DEMO VIDEO instead...
    echo.
    timeout /t 2 /nobreak >nul
    
    if exist "DEMO_VIDEO.mp4" (
        start "" "DEMO_VIDEO.mp4"
    ) else (
        echo  ERROR: DEMO_VIDEO.mp4 not found!
        pause
    )
    exit /b 1
)

echo  [OK] PHP Version: 
php --version | findstr /R "PHP"
echo.

:: ============================================================================
:: STEP 2: DATABASE AUTOMATION
:: ============================================================================
echo [2/4] Configuring Database...

:: Check if MySQL is accessible
mysql --version >nul 2>&1
if errorlevel 1 (
    echo  [WARNING] MySQL not found in PATH. Skipping database setup.
    echo  Please ensure XAMPP MySQL is running manually.
    echo.
    goto :skip_db
)

:: Create database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS optispace_db;" 2>nul
if errorlevel 1 (
    echo  [WARNING] Could not create database. It may already exist.
) else (
    echo  [OK] Database 'optispace_db' created/verified
)

:: Import SQL file
if exist "Database_SQL\optispace_db.sql" (
    mysql -u root optispace_db < "Database_SQL\optispace_db.sql" 2>nul
    if errorlevel 1 (
        echo  [WARNING] Database import failed. Using existing data.
    ) else (
        echo  [OK] Database schema imported successfully
    )
) else (
    if exist "database.sql" (
        mysql -u root optispace_db < "database.sql" 2>nul
        echo  [OK] Database configured from database.sql
    ) else (
        echo  [WARNING] SQL file not found. Using existing database.
    )
)

echo  [OK] Database Configuration Complete
echo.

:skip_db

:: ============================================================================
:: STEP 3: LAUNCH WEB SERVER
:: ============================================================================
echo [3/4] Starting PHP Built-in Web Server...
echo  Server Address: http://localhost:8000
echo  Document Root: %CD%
echo.

:: ============================================================================
:: STEP 4: AUTO-OPEN BROWSER
:: ============================================================================
echo [4/4] Launching Dashboard...
timeout /t 2 /nobreak >nul
start http://localhost:8000

echo.
echo ╔════════════════════════════════════════════════════════════════╗
echo ║                  SYSTEM STATUS: ONLINE                         ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.
echo  Dashboard: http://localhost:8000
echo  Press CTRL+C to stop the server
echo.
echo ════════════════════════════════════════════════════════════════
echo.

:: Start PHP server (this will keep the window open)
php -S localhost:8000

:: If server stops, show message
echo.
echo  Server stopped. Press any key to exit...
pause >nul
