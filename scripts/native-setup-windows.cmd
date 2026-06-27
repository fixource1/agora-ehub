@echo off
REM Wrapper: runs PowerShell scripts without changing system execution policy
cd /d "%~dp0\.."
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0native-setup-windows.ps1" %*
