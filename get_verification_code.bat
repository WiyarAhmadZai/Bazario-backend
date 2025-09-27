@echo off
setlocal enabledelayedexpansion

REM Simple script to get verification code for testing purposes
REM Usage: get_verification_code.bat user@example.com

if "%1"=="" (
    echo Usage: %0 ^<email^>
    exit /b 1
)

set EMAIL=%1

echo Getting verification code for: %EMAIL%

REM Make API request to get verification code
curl -X POST http://localhost:8000/api/get-verification-code ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\": \"%EMAIL%\"}"

echo.