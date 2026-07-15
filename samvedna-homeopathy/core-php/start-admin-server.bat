@echo off
REM ============================================================
REM  Samvedna Homeopathy - Core PHP admin server launcher
REM ============================================================
REM  Double-click this file (or run it from a terminal) to start
REM  the PHP backend that powers the admin panel and JSON API.
REM
REM  Requirements:
REM    1. XAMPP MySQL/MariaDB must be running (start it in the
REM       XAMPP Control Panel).
REM    2. Keep this window open while using the admin. Ctrl+C to stop.
REM
REM  Then open:  http://localhost:3000/admin   (with `pnpm dev` running)
REM         or:  http://localhost:8080/admin   (directly)
REM  Login:  admin / admin123
REM ============================================================

setlocal
REM Move to the Next.js project root (one level up from core-php).
cd /d "%~dp0.."

REM Prefer PHP on PATH; fall back to the XAMPP install.
set "PHP=php"
where php >nul 2>nul || set "PHP=C:\xampp\php\php.exe"

if not exist "%PHP%" (
  echo [ERROR] PHP was not found on PATH or at C:\xampp\php\php.exe
  echo         Install XAMPP or add php.exe to your PATH, then retry.
  pause
  exit /b 1
)

echo.
echo  Starting Samvedna admin backend on http://localhost:8080 ...
echo  Admin:  http://localhost:3000/admin   (or http://localhost:8080/admin)
echo  Login:  admin / admin123
echo  Make sure XAMPP MySQL is running. Press Ctrl+C to stop.
echo.

"%PHP%" -S localhost:8080 -t core-php core-php\router.php
