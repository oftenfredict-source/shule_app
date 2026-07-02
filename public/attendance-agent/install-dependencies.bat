@echo off
cd /d "%~dp0"
echo Installing ZKTeco library for Attendance Agent...
composer install --no-interaction
if errorlevel 1 (
    echo.
    echo FAILED. Ensure Composer is installed and Laragon is in PATH.
    echo Try: Laragon Menu -^> Tools -^> Composer -^> Install
    pause
    exit /b 1
)
echo.
echo Done. vendor folder is ready. You can open the agent in your browser.
pause
