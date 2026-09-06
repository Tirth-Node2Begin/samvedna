@echo off
REM Samvedna CMS - Core PHP backend on port 8001.
REM Run migrate.php first if you have not: php backend\migrations\migrate.php --seed-demo
cd /d "%~dp0"
echo Samvedna CMS backend  http://127.0.0.1:8001/api
php -S 127.0.0.1:8001 -t backend backend\router.php
