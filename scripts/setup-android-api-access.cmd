@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup-android-api-access.ps1" -UpdateNativeEnv %*
