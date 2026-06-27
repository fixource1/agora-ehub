@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0configure-windows-php.ps1" %*
