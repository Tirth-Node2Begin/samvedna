@echo off
REM Samvedna CMS - start MySQL, the Core PHP API and the Next.js frontend.
REM
REM   start-cms.bat            normal start
REM   start-cms.bat -Fresh     rebuild the demo data first
REM   start-cms.bat -Seed      run migrations + seed, keeping existing data
REM
REM Double-clicking this file works; it just wraps start-cms.ps1 so you do not
REM have to think about the PowerShell execution policy.
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-cms.ps1" %*
pause
