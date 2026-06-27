@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0apply-nativephp-env.ps1" %*
