@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0prepare-native-build-windows.ps1" %*
